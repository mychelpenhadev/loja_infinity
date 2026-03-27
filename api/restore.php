<?php
error_reporting(0);
ini_set('display_errors', '0');
ini_set('max_execution_time', '300');
ob_start();

function restore_json_response($data, $code = 200) {
    while (ob_get_level()) @ob_end_clean();
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function rmdir_recursive($dir) {
    if (!is_dir($dir)) return;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            rmdir_recursive($path);
        } else {
            @unlink($path);
        }
    }
    @rmdir($dir);
}

require_once 'security.php';
require_once 'db.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    restore_json_response(["status" => "error", "message" => "Método não permitido"], 405);
}

if (!isset($_FILES['backup']) || $_FILES['backup']['error'] !== UPLOAD_ERR_OK) {
    $errorCode = isset($_FILES['backup']) ? $_FILES['backup']['error'] : 'FILES vazio';
    $uploadMax = ini_get('upload_max_filesize');
    $postMax = ini_get('post_max_size');
    restore_json_response([
        "status" => "error",
        "message" => "Erro no upload. Código: $errorCode | upload_max_filesize: $uploadMax | post_max_size: $postMax"
    ], 400);
}

$file = $_FILES['backup'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if ($ext !== 'zip') {
    restore_json_response(["status" => "error", "message" => "Arquivo inválido. Envie um arquivo .zip"], 400);
}

if ($file['size'] > 500 * 1024 * 1024) {
    restore_json_response(["status" => "error", "message" => "Arquivo muito grande. Máximo 500MB"], 400);
}

if (!class_exists('ZipArchive')) {
    restore_json_response(["status" => "error", "message" => "A extensão ZIP do PHP não está instalada no servidor."], 500);
}

$tmpDir = sys_get_temp_dir() . '/restore_' . uniqid();

try {
    if (!@mkdir($tmpDir, 0755, true) && !is_dir($tmpDir)) {
        throw new Exception("Não foi possível criar diretório temporário");
    }

    $zip = new ZipArchive();
    if ($zip->open($file['tmp_name']) !== true) {
        throw new Exception("Não foi possível abrir o arquivo ZIP");
    }

    $zip->extractTo($tmpDir);
    $zip->close();

    $sqlFile = $tmpDir . '/backup.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Arquivo backup.sql não encontrado no ZIP. O arquivo pode não ser um backup válido.");
    }

    $sqlContent = file_get_contents($sqlFile);
    if ($sqlContent === false || empty($sqlContent)) {
        throw new Exception("Arquivo backup.sql está vazio ou não pôde ser lido.");
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    $pdo->exec("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");

    $tables = ['orders', 'products', 'users', 'configs'];
    foreach ($tables as $table) {
        try {
            $pdo->exec("DELETE FROM `$table`");
        } catch (PDOException $e) { }
    }

    $sqlContent = preg_replace('/^--.*$/m', '', $sqlContent);
    $sqlContent = preg_replace('/^SET\s+.*;$/mi', '', $sqlContent);
    $sqlContent = str_replace("\r\n", "\n", $sqlContent);
    $sqlContent = str_replace('utf8mb4_0900_ai_ci', 'utf8mb4_unicode_ci', $sqlContent);

    $statements = [];
    $currentStmt = '';
    $inString = false;
    $stringChar = '';
    $escaped = false;
    $len = strlen($sqlContent);

    for ($i = 0; $i < $len; $i++) {
        $c = $sqlContent[$i];
        if ($escaped) {
            $currentStmt .= $c;
            $escaped = false;
            continue;
        }
        if ($c === '\\') {
            $currentStmt .= $c;
            $escaped = true;
            continue;
        }
        if (!$inString) {
            if ($c === "'" || $c === '"' || $c === '`') {
                $inString = true;
                $stringChar = $c;
            } else if ($c === ';') {
                $statements[] = trim($currentStmt);
                $currentStmt = '';
                continue;
            }
        } else {
            if ($c === $stringChar) {
                $inString = false;
            }
        }
        $currentStmt .= $c;
    }
    if (trim($currentStmt) !== '') {
        $statements[] = trim($currentStmt);
    }

    $errors = [];
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (empty($stmt)) continue;

        try {
            $pdo->exec($stmt);
        } catch (PDOException $e) {
            $msgErr = $e->getMessage();
            if (strpos($msgErr, '2006') !== false || strpos($msgErr, 'gone away') !== false || strpos($msgErr, '1153') !== false || strpos($msgErr, 'max_allowed_packet') !== false) {
                $errors[] = "Pulado: imagem/produto muito grande para o servidor.";
                if (strpos($msgErr, '2006') !== false || strpos($msgErr, 'gone away') !== false) {
                    try { $pdo = null; include 'db.php'; } catch(Exception $ex) {}
                    try { $pdo->exec("SET FOREIGN_KEY_CHECKS=0"); } catch(Exception $ex) {}
                    try { $pdo->exec("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'"); } catch(Exception $ex) {}
                }
            } else {
                $errors[] = $msgErr;
            }
        }
    }

    foreach ($tables as $table) {
        try { $pdo->exec("ALTER TABLE `$table` AUTO_INCREMENT = 1"); } catch (PDOException $e) {}
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

    $cacheDir = __DIR__ . "/cache";
    if (is_dir($cacheDir)) {
        $files = glob($cacheDir . "/*.json");
        foreach ($files as $f) {
            if (is_file($f)) @unlink($f);
        }
    }

    $basePath = __DIR__ . '/../';
    $imageDirs = ['uploads/products/', 'uploads/profile_pics/', 'uploads/banners/'];
    foreach ($imageDirs as $dir) {
        $srcDir = $tmpDir . '/' . $dir;
        $dstDir = $basePath . $dir;
        if (!is_dir($dstDir)) @mkdir($dstDir, 0777, true);
        @chmod($dstDir, 0777);
        if (is_dir($srcDir)) {
            $backupFiles = @scandir($srcDir);
            if ($backupFiles) {
                // Limpa arquivos existentes no destino (mantém .gitkeep)
                $existingFiles = @scandir($dstDir);
                if ($existingFiles) {
                    foreach ($existingFiles as $ef) {
                        if ($ef === '.' || $ef === '..' || $ef === '.gitkeep') continue;
                        @unlink($dstDir . $ef);
                    }
                }
                // Copia arquivos do backup
                foreach ($backupFiles as $f) {
                    if ($f === '.' || $f === '..' || $f === '.gitkeep') continue;
                    $src = $srcDir . $f;
                    $dst = $dstDir . $f;
                    if (is_file($src)) {
                        if (!@copy($src, $dst)) {
                            $errors[] = "Falha ao copiar imagem: $f";
                        } else {
                            @chmod($dst, 0666);
                        }
                    }
                }
            }
        }
    }

    @rmdir_recursive($tmpDir);

    $msg = "Restore concluído com sucesso!";
    if (count($errors) > 0) {
        $msg .= " Avisos: " . implode(' | ', array_slice($errors, 0, 3));
    }
    restore_json_response(["status" => "success", "message" => $msg]);

} catch (Exception $e) {
    if (isset($pdo)) {
        try { $pdo->exec("SET FOREIGN_KEY_CHECKS=1"); } catch(Exception $ex) {}
    }
    @rmdir_recursive($tmpDir);
    restore_json_response(["status" => "error", "message" => mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8')], 500);
}
