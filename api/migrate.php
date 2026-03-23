<?php
require_once 'db.php';

// Script para migrar o banco de dados de localStorage para MySQL
// Este script deve ser executado para configurar as tabelas iniciais

try {
    // 1. Criar tabela de Produtos
    $pdo->exec("CREATE TABLE IF NOT EXISTS `products` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `external_id` varchar(50) DEFAULT NULL,
        `name` varchar(255) NOT NULL,
        `description` text DEFAULT NULL,
        `price` decimal(10,2) NOT NULL,
        `category` varchar(100) DEFAULT NULL,
        `brand` varchar(100) DEFAULT NULL,
        `image` LONGTEXT DEFAULT NULL,
        `video` varchar(255) DEFAULT NULL,
        `rating` decimal(3,1) DEFAULT 5.0,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Criar tabela de Pedidos
    $pdo->exec("CREATE TABLE IF NOT EXISTS `orders` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `external_id` varchar(50) NOT NULL,
        `user_id` int(11) DEFAULT NULL,
        `user_name` varchar(100) DEFAULT NULL,
        `total` decimal(10,2) NOT NULL,
        `status` varchar(50) DEFAULT 'pendente',
        `items_json` LONGTEXT DEFAULT NULL,
        `method` varchar(50) DEFAULT 'WhatsApp',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 3. Criar tabela de Configurações
    $pdo->exec("CREATE TABLE IF NOT EXISTS `configs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `config_key` varchar(100) NOT NULL UNIQUE,
        `config_value` text DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 4. Inserir WhatsApp padrão
    $stmt = $pdo->prepare("INSERT IGNORE INTO `configs` (`config_key`, `config_value`) VALUES (?, ?)");
    $stmt->execute(['whatsappNumber', '+5598985269184']);

    header('Content-Type: application/json');
    echo json_encode(["status" => "success", "message" => "Banco de dados migrado com sucesso!"]);
} catch (Throwable $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erro na migração: " . $e->getMessage()]);
}
