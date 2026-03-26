<?php
ob_start();
require_once 'security.php';
require_once 'db.php';
ob_clean();

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método não permitido"]);
    exit;
}

try {
    $zip = new ZipArchive();
    $tmpFile = tempnam(sys_get_temp_dir(), 'backup_');
    $tmpZip = $tmpFile . '.zip';
    rename($tmpFile, $tmpZip);

    if ($zip->open($tmpZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new Exception("Não foi possível criar o arquivo ZIP");
    }

    $sqlDump = "-- Backup da Loja Infinity Variedades\n";
    $sqlDump .= "-- Data: " . date('Y-m-d H:i:s') . "\n";
    $sqlDump .= "-- Banco: papelaria_db\n\n";
    $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n";
    $sqlDump .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n";
    $sqlDump .= "SET NAMES utf8mb4;\n\n";

    $tables = ['products', 'users', 'orders', 'configs'];

    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $sqlDump .= "-- Estrutura da tabela `$table`\n";
        $sqlDump .= "DROP TABLE IF EXISTS `$table`;\n";
        $sqlDump .= $row['Create Table'] . ";\n\n";

        $stmt = $pdo->query("SELECT * FROM `$table`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) > 0) {
            $sqlDump .= "-- Dados da tabela `$table`\n";
            foreach ($rows as $row) {
                $columns = array_map(function ($col) {
                    return "`$col`";
                }, array_keys($row));

                $values = array_map(function ($val) use ($pdo) {
                    if ($val === null) return 'NULL';
                    return $pdo->quote($val);
                }, array_values($row));

                $sqlDump .= "INSERT INTO `$table` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
            }
            $sqlDump .= "\n";
        }
    }

    $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";
    $zip->addFromString('backup.sql', $sqlDump);

    $uploadDirs = [
        'uploads/products/',
        'uploads/profile_pics/',
        'uploads/banners/',
    ];

    $basePath = __DIR__ . '/../';

    foreach ($uploadDirs as $dir) {
        $fullDir = $basePath . $dir;
        if (is_dir($fullDir)) {
            $files = scandir($fullDir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                $filePath = $fullDir . $file;
                if (is_file($filePath)) {
                    $zip->addFile($filePath, $dir . $file);
                }
            }
        }
    }

    $zip->close();

    $filename = 'backup_loja_' . date('Y-m-d_His') . '.zip';

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($tmpZip));
    header('Pragma: no-cache');
    header('Expires: 0');

    readfile($tmpZip);
    unlink($tmpZip);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
