<?php
ob_start();
set_time_limit(300);
require_once 'security.php';
require_once 'db.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método não permitido"]);
    exit;
}

if (!isset($_FILES['backup']) || $_FILES['backup']['error'] !== UPLOAD_ERR_OK) {
    $errorCode = isset($_FILES['backup']) ? $_FILES['backup']['error'] : 'FILES vazio';
    $uploadMax = ini_get('upload_max_filesize');
    $postMax = ini_get('post_max_size');
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Erro no upload. Código: $errorCode | upload_max_filesize: $uploadMax | post_max_size: $postMax"
    ]);
    exit;
}

$file = $_FILES['backup'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if ($ext !== 'zip') {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Arquivo inválido. Envie um arquivo .zip"]);
    exit;
}

if ($file['size'] > 500 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Arquivo muito grande. Máximo 500MB"]);
    exit;
}

$tmpDir = sys_get_temp_dir() . '/restore_' . uniqid();
mkdir($tmpDir, 0755, true);

if (!class_exists('ZipArchive')) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "A extensão ZIP do PHP não está instalada no servidor. Não é possível restaurar o backup."]);
    exit;
}

try {
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

    // Tenta aumentar o limite de pacote do MySQL para aceitar imagens gigantes (Base64)
    // No Railway o usuário costuma ser root, então isso deve funcionar.
    try {
        $pdo->exec("SET GLOBAL max_allowed_packet = 134217728"); // 128 MB
        // Fechar e reabrir a conexão para a configuração global surtir efeito nesta sessão
        $pdo = null;
        include 'db.php';
    } catch (Exception $e) {
        // Ignora caso não tenha permissão SUPER
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
    
    // Normalize newlines to ensure consistent splitting
    $sqlContent = str_replace("\r\n", "\n", $sqlContent);
    
    // Fix collation issues between MySQL 8 (XAMPP) and older MariaDB/MySQL versions (Railway)
    $sqlContent = str_replace('utf8mb4_0900_ai_ci', 'utf8mb4_unicode_ci', $sqlContent);
    
    // Robust SQL splitting that respects quoted strings
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
                // Peek ahead for newline to maintain the "split by semicolon at end of line" behavior
                // but actually any semicolon outside a string acts as a delimiter in SQL.
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
            
            // Se o MySQL fechar a conexão (ex: max_allowed_packet atingido por causa de imagem gigante)
            if (strpos($msgErr, '2006') !== false || strpos($msgErr, 'gone away') !== false || strpos($msgErr, '1153') !== false || strpos($msgErr, 'max_allowed_packet') !== false) {
                $errors[] = "Pulado: Uma imagem/produto era tão grande que o servidor recusou (Tamanho excedido).";
                
                if (strpos($msgErr, '2006') !== false || strpos($msgErr, 'gone away') !== false) {
                    $pdo = null;
                    include 'db.php'; // Reconecta!
                    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
                    $pdo->exec("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");
                }
            } else {
                if (stripos($stmt, 'CREATE TABLE') !== false) {
                    $msgErr = 'ERRO NO CREATE ' . substr($stmt, 0, 30) . '... -> ' . $msgErr;
                }
                $errors[] = $msgErr;
            }
        }
    }

    foreach ($tables as $table) {
        try {
            $pdo->exec("ALTER TABLE `$table` AUTO_INCREMENT = 1");
        } catch (PDOException $e) {}
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

    $cacheDir = __DIR__ . "/cache";
    if (is_dir($cacheDir)) {
        $files = glob($cacheDir . "/*.json");
        foreach ($files as $f) {
            if (is_file($f)) unlink($f);
        }
    }

    $basePath = __DIR__ . '/../';
    $imageDirs = ['uploads/products/', 'uploads/profile_pics/', 'uploads/banners/'];

    foreach ($imageDirs as $dir) {
        $srcDir = $tmpDir . '/' . $dir;
        $dstDir = $basePath . $dir;

        if (!is_dir($dstDir)) mkdir($dstDir, 0755, true);

        if (is_dir($srcDir)) {
            $files = scandir($srcDir);
            foreach ($files as $f) {
                if ($f === '.' || $f === '..') continue;
                $src = $srcDir . $f;
                $dst = $dstDir . $f;
                if (is_file($src)) {
                    copy($src, $dst);
                }
            }
        }
    }

    rmdir_recursive($tmpDir);

    $msg = "Restore concluído com sucesso!";
    if (count($errors) > 0) {
        $msg .= " Avisos: " . implode(' | ', array_slice($errors, 0, 3));
    }

    $msg = mb_convert_encoding($msg, 'UTF-8', 'UTF-8');
    
    @ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["status" => "success", "message" => $msg], JSON_INVALID_UTF8_SUBSTITUTE);

} catch (Exception $e) {
    if (isset($pdo)) {
        try { $pdo->exec("SET FOREIGN_KEY_CHECKS=1"); } catch(PDOException $ex) {}
    }
    if (isset($tmpDir) && is_dir($tmpDir)) @rmdir_recursive($tmpDir);
    
    @ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["status" => "error", "message" => mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8')], JSON_INVALID_UTF8_SUBSTITUTE);
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
            unlink($path);
        }
    }
    rmdir($dir);
}
