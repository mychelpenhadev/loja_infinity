<?php
error_reporting(0);
ini_set('display_errors', '0');

ob_start();
require_once 'security.php';
require_once 'db.php';

requireAdmin();

if (!isset($pdo) || !$pdo) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Banco de dados não conectado"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Método não permitido"]);
    exit;
}

@ob_end_clean();

if (!class_exists('ZipArchive')) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "A extensão ZIP não está instalada no servidor. Não é possível gerar o backup."]);
    exit;
}

try {
    $tmpDir = sys_get_temp_dir();
    $tmpZip = $tmpDir . '/backup_' . uniqid() . '.zip';
    
    $zip = new ZipArchive();

    if ($zip->open($tmpZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new Exception("Não foi possível criar o arquivo ZIP");
    }

    $sqlDump = "-- Backup da Loja\n";
    $sqlDump .= "-- Data: " . date('Y-m-d H:i:s') . "\n\n";
    $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n";
    $sqlDump .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n";
    $sqlDump .= "SET NAMES utf8mb4;\n\n";

    $tables = ['products', 'users', 'orders', 'configs', 'migrations'];

    foreach ($tables as $table) {
        try {
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
                    $columns = array_map(function ($col) { return "`$col`"; }, array_keys($row));
                    $values = array_map(function ($val) use ($pdo) {
                        if ($val === null) return 'NULL';
                        return $pdo->quote($val);
                    }, array_values($row));
                    $sqlDump .= "INSERT INTO `$table` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                }
                $sqlDump .= "\n";
            }
        } catch (Exception $e) {
            $sqlDump .= "-- Erro ao exportar tabela `$table`: " . $e->getMessage() . "\n\n";
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

    if (!file_exists($tmpZip) || filesize($tmpZip) === 0) {
        throw new Exception("Arquivo ZIP vazio ou não criado");
    }

    $filename = 'backup_loja_' . date('Y-m-d_His') . '.zip';
    $fileSize = filesize($tmpZip);

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    $fp = fopen($tmpZip, 'rb');
    while (!feof($fp)) {
        echo fread($fp, 8192);
        flush();
    }
    fclose($fp);
    unlink($tmpZip);
    exit;

} catch (Exception $e) {
    while (ob_get_level()) @ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    if (isset($tmpZip) && file_exists($tmpZip)) unlink($tmpZip);
}
