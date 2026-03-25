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

$host = getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: '127.0.0.1');
$user = getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: 'root');
$pass = getenv('DB_PASS') ?: (getenv('MYSQLPASSWORD') ?: '');
$dbname = getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: 'papelaria_db');
$port = getenv('MYSQLPORT') ?: (getenv('DB_PORT') ?: '3306');

if ($host === 'localhost') {
    $host = '127.0.0.1';
}
try {

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch (Throwable $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    die(json_encode([
        "status" => "error",
        "message" => "Erro de conexão com o banco de dados ($host): " . $e->getMessage(),
        "details" => [
            "user" => $user,
            "port" => $port,
            "dbname" => $dbname
        ]
    ]));
}
