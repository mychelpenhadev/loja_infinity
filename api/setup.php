<?php
require_once 'db.php';
try {
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('cliente', 'admin') DEFAULT 'cliente',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    if ($stmt->fetchColumn() == 0) {
        $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Administrador Geral', 'admin@infinity.com.br', '$adminPass', 'admin')");
        echo "Banco de dados configurado com sucesso! Admin padrão criado (Email: admin@infinity.com.br / Senha: admin123).";
    } else {
        echo "Banco de dados pronto e verificado! (Admin já existia).";
    }
} catch(PDOException $e) {
    die("Erro ao configurar BD: " . $e->getMessage());
}
?>
