<?php
$envPath = __DIR__ . '/../.env';
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'papelaria_db';

if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
    if ($env !== false) {
        $host = $env['DB_HOST'] ?? $host;
        $user = $env['DB_USER'] ?? $user;
        $pass = $env['DB_PASS'] ?? $pass;
        $dbname = $env['DB_NAME'] ?? $dbname;
    }
}

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->query("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->query("USE $dbname");
}
catch (PDOException $e) {
    die(json_encode(["error" => "ERRO DE CONEXÃO BD: " . $e->getMessage()]));
}
?>
