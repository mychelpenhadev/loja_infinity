<?php
ob_start();
header('Content-Type: application/json');
require_once 'db.php';
require_once 'security.php'; 
ob_clean(); 

$action = $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            header('Cache-Control: public, max-age=60');
            
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $limit;
            $category = $_GET['cat'] ?? null;
            
            $where = "";
            $params = [];
            if ($category && $category !== 'all') {
                $where = "WHERE category LIKE ?";
                $params[] = "%$category%";
            }
            
            // Get total for pagination
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products $where");
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();
            
            // Get paginated products
            $stmt = $pdo->prepare("SELECT * FROM products $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                "products" => $products,
                "pagination" => [
                    "total" => (int)$total,
                    "page" => $page,
                    "limit" => $limit,
                    "pages" => ceil($total / $limit)
                ]
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'save':
            requireAdmin();
            
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
                // Handle image (if it's base64, save to file)
                if (strpos($image, 'data:image/') === 0) {
                    list($type, $imgData) = explode(';', $image);
                    list(, $imgData) = explode(',', $imgData);
                    $imgData = base64_decode($imgData);
                    
                    $extension = explode('/', $type)[1];
                    if ($extension === 'jpeg') $extension = 'jpg';
                    
                    $filename = 'prod_' . ($id ?: 'new') . '_' . time() . '.' . $extension;
                    $uploadPath = __DIR__ . '/../uploads/products/' . $filename;
                    
                    if (file_put_contents($uploadPath, $imgData)) {
                        $image = 'uploads/products/' . $filename;
                    }
                }

                $stmt = $pdo->prepare("UPDATE products SET name=?, price=?, category=?, brand=?, image=?, video=?, description=? WHERE id=?");
                $stmt->execute([$name, $price, $category, $brand, $image, $video, $description, $id]);
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO products (name, price, category, brand, image, video, description, rating) VALUES (?, ?, ?, ?, ?, ?, ?, 5.0)");
                $stmt->execute([$name, $price, $category, $brand, $image, $video, $description]);
                $id = $pdo->lastInsertId();

                // If image was base64, we need to update it now with the correct filename using ID
                if (strpos($image, 'data:image/') === 0) {
                    list($type, $imgData) = explode(';', $image);
                    list(, $imgData) = explode(',', $imgData);
                    $imgData = base64_decode($imgData);
                    
                    $extension = explode('/', $type)[1];
                    if ($extension === 'jpeg') $extension = 'jpg';
                    
                    $filename = 'prod_' . $id . '_' . time() . '.' . $extension;
                    $uploadPath = __DIR__ . '/../uploads/products/' . $filename;
                    
                    if (file_put_contents($uploadPath, $imgData)) {
                        $newPath = 'uploads/products/' . $filename;
                        $stmt = $pdo->prepare("UPDATE products SET image=? WHERE id=?");
                        $stmt->execute([$newPath, $id]);
                    }
                }
            }

            echo json_encode(["status" => "success", "id" => $id]);
            break;

        case 'delete':
            requireAdmin();
            $id = $_GET['id'] ?? null;
            if (!$id) throw new Exception("ID não fornecido");
            
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(["status" => "success"]);
            break;

        case 'get':
            header('Cache-Control: public, max-age=3600'); // Single product cache for 1h
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
