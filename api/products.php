<?php
ob_start();
header('Content-Type: application/json');
require_once 'db.php';
require_once 'auth.php'; // Para checagem de admin se necessário

$action = $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $json = json_encode($products, JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                throw new Exception("Erro ao serializar produtos: " . json_last_error_msg());
            }
            echo $json;
            break;

        case 'save':
            // Apenas admin pode salvar/editar
            // if (!isAdmin()) { throw new Exception("Não autorizado"); }
            
            $raw_data = file_get_contents('php://input');
            $data = json_decode($raw_data, true);
            
            if (!$data) throw new Exception("Dados inválidos");

            $id = $data['id'] ?? null;
            $name = $data['name'];
            $price = $data['price'];
            $category = $data['category'];
            $brand = $data['brand'] ?? '';
            $image = $data['image'];
            $video = $data['video'] ?? '';
            $description = $data['description'] ?? '';

            if ($id && is_numeric($id)) {
                // Update
                $stmt = $pdo->prepare("UPDATE products SET name=?, price=?, category=?, brand=?, image=?, video=?, description=? WHERE id=?");
                $stmt->execute([$name, $price, $category, $brand, $image, $video, $description, $id]);
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO products (name, price, category, brand, image, video, description, rating) VALUES (?, ?, ?, ?, ?, ?, ?, 5.0)");
                $stmt->execute([$name, $price, $category, $brand, $image, $video, $description]);
                $id = $pdo->lastInsertId();
            }

            echo json_encode(["status" => "success", "id" => $id]);
            break;

        case 'delete':
            // if (!isAdmin()) { throw new Exception("Não autorizado"); }
            $id = $_GET['id'] ?? null;
            if (!$id) throw new Exception("ID não fornecido");
            
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(["status" => "success"]);
            break;

        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) throw new Exception("ID não fornecido");
            
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($product);
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
