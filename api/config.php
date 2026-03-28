<?php
if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
    ob_start("ob_gzhandler");
} else {
    ob_start();
}
header('Content-Type: application/json');
require_once 'security.php';
require_once 'db.php';
ob_clean();

try { $pdo->exec("ALTER TABLE configs MODIFY COLUMN config_value LONGTEXT DEFAULT NULL"); } catch(Exception $e) {}

$action = $_GET['action'] ?? 'get';
try {
    switch ($action) {
        case 'get':
            header('Cache-Control: private, max-age=300');
            $key = $_GET['key'] ?? null;
            if ($key) {
                $stmt = $pdo->prepare("SELECT config_value FROM configs WHERE config_key = ?");
                $stmt->execute([$key]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(["value" => $row['config_value'] ?? null]);
            } else {
                $stmt = $pdo->query("SELECT config_key, config_value FROM configs");
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
                if ($key === 'hero_banners') {
                    // Old file-based banners cleanup no longer needed (base64 stored in DB)
                }
                $stmt = $pdo->prepare("INSERT INTO configs (config_key, config_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE config_value = ?");
                $stmt->execute([$key, $value, $value]);
            }
            echo json_encode(["status" => "success"]);
            break;
        case 'upload-banner':
            requireAdmin();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Método não permitido");
            if (!isset($_FILES['banner']) || $_FILES['banner']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Erro no upload do arquivo");
            }
            $file = $_FILES['banner'];
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowed)) {
                throw new Exception("Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WEBP.");
            }
            if ($file['size'] > 20 * 1024 * 1024) {
                throw new Exception("Arquivo muito grande. Máximo 20MB.");
            }
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'banner_' . uniqid() . '_' . time() . '.' . $ext;
            $uploadDir = getUploadPath('banners/');
            if (!is_dir($uploadDir)) {
                if (!@mkdir($uploadDir, 0755, true)) {
                    throw new Exception("Não foi possível criar diretório: $uploadDir (UPLOAD_DIR=" . (getenv('UPLOAD_DIR') ?: 'não definido') . ")");
                }
            }
            $dest = $uploadDir . $filename;
            if (!@move_uploaded_file($file['tmp_name'], $dest)) {
                throw new Exception("Falha ao salvar em: $dest (dir gravável=" . (is_writable($uploadDir) ? 'sim' : 'não') . ", tmp=" . $file['tmp_name'] . ")");
            }
            echo json_encode(["status" => "success", "url" => "uploads/banners/" . $filename]);
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
