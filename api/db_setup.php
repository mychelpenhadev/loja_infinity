<?php
require_once 'db.php';

try {
    echo "Iniciando atualização do banco de dados...<br>";
    
    // Tenta adicionar a coluna is_verified
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0");
        echo "✅ Coluna 'is_verified' adicionada.<br>";
    } catch (Exception $e) {
        echo "ℹ️ Coluna 'is_verified' já existe ou erro: " . $e->getMessage() . "<br>";
    }

    // Tenta adicionar a coluna verification_code
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN verification_code VARCHAR(10) NULL");
        echo "✅ Coluna 'verification_code' adicionada.<br>";
    } catch (Exception $e) {
        echo "ℹ️ Coluna 'verification_code' já existe ou erro: " . $e->getMessage() . "<br>";
    }

    // Marca usuários antigos como verificados
    $pdo->exec("UPDATE users SET is_verified = 1 WHERE is_verified = 0 OR is_verified IS NULL");
    echo "✅ Usuários antigos marcados como verificados.<br>";

    echo "<br><b>Tudo pronto! O sistema de segurança agora está operacional.</b><br>";
    echo "Por segurança, delete este arquivo (api/db_setup.php) após o uso.";

} catch (Exception $e) {
    die("❌ Erro fatal na migração: " . $e->getMessage());
}
