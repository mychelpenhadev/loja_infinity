-- Tabelas adicionais para persistência no banco de dados

-- Tabela de Produtos
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `external_id` varchar(50) DEFAULT NULL, -- Para compatibilidade com os IDs 'p1', 'p2', etc
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `image` LONGTEXT DEFAULT NULL, -- Armazena Base64 ou URL
  `video` varchar(255) DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT 5.0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Pedidos
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `external_id` varchar(50) NOT NULL, -- ORD12345
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'pendente',
  `items_json` LONGTEXT DEFAULT NULL, -- Armazena os itens como JSON
  `method` varchar(50) DEFAULT 'WhatsApp',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Configuração
CREATE TABLE IF NOT EXISTS `configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL UNIQUE,
  `config_value` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserir produtos iniciais caso a tabela esteja vazia
INSERT INTO `products` (`external_id`, `name`, `description`, `price`, `category`, `image`, `rating`) 
SELECT 'p1', 'Caderno Inteligente Tons Pastéis', 'Caderno de discos com folhas reposicionáveis.', 89.90, 'cadernos', 'https://images.unsplash.com/photo-1531346878377-a541e4ab0eaf?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', 5.0
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `external_id` = 'p1');

INSERT INTO `products` (`external_id`, `name`, `description`, `price`, `category`, `image`, `rating`) 
SELECT 'p2', 'Kit Canetas Gel Pastel', 'Conjunto com 6 cores incríveis.', 34.50, 'canetas', 'https://images.unsplash.com/photo-1585336261022-680e295ce3fe?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', 4.5
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `external_id` = 'p2');

-- Inserir WhatsApp padrão
INSERT INTO `configs` (`config_key`, `config_value`)
SELECT 'whatsappNumber', '+5598985269184'
WHERE NOT EXISTS (SELECT 1 FROM `configs` WHERE `config_key` = 'whatsappNumber');
