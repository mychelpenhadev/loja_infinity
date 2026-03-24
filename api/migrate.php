<?php
require_once 'db.php';

// Script para migrar o banco de dados de localStorage para MySQL
// Este script deve ser executado para configurar as tabelas iniciais
require_once 'security.php';
requireAdmin();

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

    // 4. Criar tabela de Usuários
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `cpf` varchar(20) DEFAULT NULL,
        `telefone` varchar(20) DEFAULT NULL,
        `email` varchar(100) NOT NULL,
        `password` varchar(255) NOT NULL,
        `role` enum('cliente','admin') DEFAULT 'cliente',
        `profile_picture` LONGTEXT DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `email` (`email`),
        UNIQUE KEY `cpf` (`cpf`),
        UNIQUE KEY `telefone` (`telefone`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 5. Inserir WhatsApp padrão
    $stmt = $pdo->prepare("INSERT IGNORE INTO `configs` (`config_key`, `config_value`) VALUES (?, ?)");
    $stmt->execute(['whatsappNumber', '+5598985269184']);

    // 6. Inserir Admin padrão
    $stmt = $pdo->prepare("INSERT IGNORE INTO `users` (id, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([1, 'Administrador Geral', 'admin@infinity.com.br', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);

    // 7. Inserir Produtos Iniciais (Seed) if empty
    $count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($count == 0) {
        $initialProducts = [
            ['p1', 'Caderno Inteligente Tons Pastéis', 'Caderno de discos com folhas reposicionáveis.', 89.90, 'cadernos', 'https://images.unsplash.com/photo-1531346878377-a541e4ab0eaf?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'],
            ['p2', 'Kit Canetas Gel Pastel', 'Conjunto com 6 cores incríveis.', 34.50, 'canetas', 'https://images.unsplash.com/photo-1585336261022-680e295ce3fe?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'],
            ['p3', 'Agulha Amigurumi Soft', 'Agulha ergonômica para crochê.', 15.50, 'linhas', 'https://images.unsplash.com/photo-1591815302525-756a9bcc3425?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO products (external_id, name, description, price, category, image, rating) VALUES (?, ?, ?, ?, ?, ?, 5.0)");
        foreach ($initialProducts as $p) {
            $stmt->execute($p);
        }
    }

    header('Content-Type: application/json');
    echo json_encode(["status" => "success", "message" => "Banco de dados migrado e povoado com sucesso!"]);
}
catch (Throwable $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erro na migração: " . $e->getMessage()]);
}