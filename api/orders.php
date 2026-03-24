<?php
header('Content-Type: application/json');
require_once 'db.php';
require_once 'security.php';

$action = $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            requireAdmin();
            $stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($orders);
            break;

        case 'save':
            $raw_data = file_get_contents('php://input');
            $data = json_decode($raw_data, true);
            if (!$data) throw new Exception("Dados de pedido inválidos");
            
            session_start();
            $external_id = $data['external_id'] ?? $data['externalId'] ?? ('ORD' . rand(1000, 9999));
            $user_id = $data['user_id'] ?? $data['userId'] ?? $_SESSION['user_id'] ?? null;
            $user_name = $data['user_name'] ?? $data['userName'] ?? $_SESSION['user_name'] ?? 'Visitante';
            $total = $data['total'];
            $status = $data['status'] ?? 'pendente';
            $method = $data['method'] ?? 'WhatsApp';
            $items_json = json_encode($data['items'] ?? $data['items_json'] ?? []);

            $stmt = $pdo->prepare("INSERT INTO orders (external_id, user_id, user_name, total, status, items_json, method) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$external_id, $user_id, $user_name, $total, $status, $items_json, $method]);
            
            echo json_encode(["status" => "success", "id" => $pdo->lastInsertId()]);
            break;

        case 'update_status':
            requireAdmin();
            $id = $_GET['id'] ?? null;
            $status = $_GET['status'] ?? null;
            if (!$id || !$status) throw new Exception("Parâmetros faltantes");

            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            echo json_encode(["status" => "success"]);
            break;
            
        case 'delete':
            requireAdmin();
            $id = $_GET['id'] ?? null;
            if (!$id) throw new Exception("ID não fornecido");
            $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(["status" => "success"]);
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
