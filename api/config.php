<?php
header('Content-Type: application/json');
require_once 'security.php';
require_once 'db.php';

$action = $_GET['action'] ?? 'get';

try {
    switch ($action) {
        case 'get':
            header('Cache-Control: private, max-age=300'); // Config cache for 5 min
            $key = $_GET['key'] ?? null;
            if ($key) {
                $stmt = $pdo->prepare("SELECT config_value FROM configs WHERE config_key = ?");
                $stmt->execute([$key]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(["value" => $row['config_value'] ?? null]);
            } else {
                $stmt = $pdo->query("SELECT * FROM configs");
                $configs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                echo json_encode($configs);
            }
            break;

        case 'save':
            requireAdmin();
            $raw_data = file_get_contents('php://input');
            $data = json_decode($raw_data, true);
            if (!$data) throw new Exception("Dados inválidos");

            foreach ($data as $key => $value) {
                $stmt = $pdo->prepare("INSERT INTO configs (config_key, config_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE config_value = ?");
                $stmt->execute([$key, $value, $value]);
            }
            echo json_encode(["status" => "success"]);
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
