<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste de Conexão MySQL</h1>";

$host = getenv('MYSQLHOST') ?: '127.0.0.1';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$db   = getenv('MYSQLDATABASE') ?: 'laravel';
$port = getenv('MYSQLPORT') ?: '3306';

echo "Tentando conectar em: $host:$port (Base: $db, Usuário: $user)...<br>";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    echo "<h2 style='color:green'>SUCESSO! Conectado ao MySQL com sucesso.</h2>";

    echo "<h3>Tabelas encontradas:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:orange'>O banco de dados está VAZIO (nenhuma tabela encontrada).</p>";
    }

} catch (PDOException $e) {
    echo "<h2 style='color:red'>ERRO DE CONEXÃO:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}

echo "<h3>Outras Variáveis detectadas:</h3>";
echo "DB_URL: " . (getenv('DB_URL') ? 'Sim (mascarado)' : 'Não encontrada') . "<br>";
echo "MYSQL_URL: " . (getenv('MYSQL_URL') ? 'Sim (mascarado)' : 'Não encontrada') . "<br>";
