<?php
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

    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    $pdo->exec("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");

    $tables = ['orders', 'products', 'users', 'configs'];
    foreach ($tables as $table) {
        $pdo->exec("DELETE FROM `$table`");
    }

    $sqlContent = preg_replace('/^--.*$/m', '', $sqlContent);
    $sqlContent = preg_replace('/^SET\s+.*;$/mi', '', $sqlContent);

    $statements = explode(";", $sqlContent);
    $errors = [];

    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (empty($stmt)) continue;
        if (!preg_match('/^INSERT\s+/i', $stmt)) continue;

        try {
            $pdo->exec($stmt);
        } catch (PDOException $e) {
            $errors[] = $e->getMessage();
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

    echo json_encode(["status" => "success", "message" => $msg]);

} catch (Exception $e) {
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    if (is_dir($tmpDir)) rmdir_recursive($tmpDir);
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
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
