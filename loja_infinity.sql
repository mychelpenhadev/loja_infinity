-- Script de importação para o Banco de Dados Loja Infinity
-- Gerado para compatibilidade com MySQL/MariaDB (XAMPP)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
CREATE DATABASE IF NOT EXISTS `loja_infinity` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `loja_infinity`;
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Estrutura da tabela `users`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `cpf` varchar(20) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('cliente','admin') NOT NULL DEFAULT 'cliente',
  `profile_picture` longtext DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_cpf_unique` (`cpf`),
  UNIQUE KEY `users_telefone_unique` (`telefone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estrutura da tabela `products`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `external_id` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `discount_percent` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `image` longtext DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `sold_quantity` int(11) NOT NULL DEFAULT 0,
  `rating` decimal(3,1) NOT NULL DEFAULT 5.0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estrutura da tabela `configs`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `configs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL,
  `config_value` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `configs_config_key_unique` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estrutura da tabela `orders`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `orders` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `external_id` varchar(50) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pendente',
  `items_json` longtext DEFAULT NULL,
  `method` varchar(50) NOT NULL DEFAULT 'WhatsApp',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estrutura da tabela `password_reset_tokens`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estrutura da tabela `sessions`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Inserção de Dados Iniciais (Seeders)
-- --------------------------------------------------------

-- Usuário Admin (Senha: admin123)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Administrador Geral', 'admin@infinity.com.br', '$2y$12$R.S/I5mR.5w50p2oW5v5p.p5v5p.p5v5p.p5v5p.p5v5p.p5v5p.', 'admin', NOW(), NOW());

-- Configurações
INSERT INTO `configs` (`config_key`, `config_value`, `created_at`, `updated_at`) VALUES
('whatsappNumber', '+5598985269184', NOW(), NOW());

-- Produtos Iniciais
INSERT INTO `products` (`external_id`, `name`, `description`, `price`, `category`, `image`, `rating`, `created_at`, `updated_at`) VALUES
('p1', 'Caderno Inteligente Tons Pastéis', 'Caderno de discos com folhas reposicionáveis.', 89.90, 'cadernos', 'https://images.unsplash.com/photo-1531346878377-a541e4ab0eaf?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', 5.0, NOW(), NOW()),
('p2', 'Kit Canetas Gel Pastel', 'Conjunto com 6 cores incríveis.', 34.50, 'canetas', 'https://images.unsplash.com/photo-1585336261022-680e295ce3fe?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', 5.0, NOW(), NOW()),
('p3', 'Agulha Amigurumi Soft', 'Agulha ergonômica para crochê.', 15.50, 'linhas', 'https://images.unsplash.com/photo-1591815302525-756a9bcc3425?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', 5.0, NOW(), NOW());

COMMIT;
