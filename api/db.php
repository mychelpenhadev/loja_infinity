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

if (getenv('MYSQL_CONN_URL')) {
    $connUrl = parse_url(getenv('MYSQL_CONN_URL'));
    $host = $connUrl['host'] ?? $host;
    $user = $connUrl['user'] ?? $user;
    $pass = $connUrl['pass'] ?? $pass;
    $dbname = ltrim($connUrl['path'], '/') ?? $dbname;
    $port = $connUrl['port'] ?? '3306';
} else {
    $host = getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: $host);
    $user = getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: $user);
    $pass = getenv('DB_PASS') ?: (getenv('MYSQLPASSWORD') ?: $pass);
    $dbname = getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: $dbname);
    $port = getenv('MYSQLPORT') ?: (getenv('DB_PORT') ?: '3306');
}

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

/**
 * Safely deletes a file from the uploads directory
 * @param string|null $relativePath Path relative to the root (e.g. 'uploads/products/xyz.jpg')
 * @return bool
 */
function deleteFileIfInUploads($relativePath) {
    if (empty($relativePath)) return false;
    // Security: Only allow deleting files inside the uploads directory
    if (strpos($relativePath, 'uploads/') !== 0) return false;
    
    $fullPath = __DIR__ . '/../' . ltrim($relativePath, '/');
    if (is_file($fullPath)) {
        return @unlink($fullPath);
    }
    return false;
}
