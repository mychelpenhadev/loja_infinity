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

// Suporte para variáveis de ambiente do sistema (como no Railway/Vercel)
$host = getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: $host);
$user = getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: $user);
$pass = getenv('DB_PASS') ?: (getenv('MYSQLPASSWORD') ?: $pass);
$dbname = getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: $dbname);
$port = getenv('MYSQLPORT') ?: '3306';

// Ajustar host para incluir porta se necessário
if ($port !== '3306' && !str_contains($host, ':')) {
    $host .= ":$port";
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    header('Content-Type: application/json');
    die(json_encode(["status" => "error", "message" => "O banco de dados não está pronto ou os dados estão incorretos: " . $e->getMessage()]));
}
?>
