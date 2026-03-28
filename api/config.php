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
            
            if (!isset($_FILES['banner'])) {
                $uploadMax = ini_get('upload_max_filesize');
                $postMax = ini_get('post_max_size');
                throw new Exception("Nenhum arquivo enviado. Limites: upload_max_filesize=$uploadMax, post_max_size=$postMax");
            }
            
            $errorCode = $_FILES['banner']['error'];
            if ($errorCode !== UPLOAD_ERR_OK) {
                $errorNames = [
                    0 => 'UPLOAD_ERR_OK',
                    1 => 'UPLOAD_ERR_INI_SIZE (excede upload_max_filesize)',
                    2 => 'UPLOAD_ERR_FORM_SIZE (excede MAX_FILE_SIZE do form)',
                    3 => 'UPLOAD_ERR_PARTIAL (upload parcial)',
                    4 => 'UPLOAD_ERR_NO_FILE (nenhum arquivo)',
                    6 => 'UPLOAD_ERR_NO_TMP_DIR (sem diretório tmp)',
                    7 => 'UPLOAD_ERR_CANT_WRITE (sem permissão para escrever)',
                    8 => 'UPLOAD_ERR_EXTENSION (extensão interrompeu)',
                ];
                $errorName = $errorNames[$errorCode] ?? "Erro desconhecido ($errorCode)";
                $tmpDir = sys_get_temp_dir();
                $tmpWritable = is_writable($tmpDir) ? 'sim' : 'não';
                throw new Exception("Erro no upload: $errorName | tmp_dir=$tmpDir (gravável=$tmpWritable) | upload_max=" . ini_get('upload_max_filesize') . " | post_max=" . ini_get('post_max_size'));
            }
            
            $file = $_FILES['banner'];
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowed)) {
                throw new Exception("Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WEBP. Recebido: " . $file['type']);
            }
            if ($file['size'] > 20 * 1024 * 1024) {
                throw new Exception("Arquivo muito grande. Máximo 20MB. Tamanho: " . round($file['size']/1024/1024, 2) . "MB");
            }
            
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'banner_' . uniqid() . '_' . time() . '.' . $ext;
            $uploadDir = getUploadPath('banners/');
            
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    throw new Exception("Não foi possível criar diretório: $uploadDir");
                }
            }
            
            if (!is_writable($uploadDir)) {
                chmod($uploadDir, 0777);
                if (!is_writable($uploadDir)) {
                    throw new Exception("Diretório não é gravável: $uploadDir");
                }
            }
            
            $dest = $uploadDir . $filename;
            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                throw new Exception("Falha ao mover arquivo para: $dest");
            }
            
            chmod($dest, 0666);
            echo json_encode(["status" => "success", "url" => "api/uploads.php?file=banners/" . $filename]);
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
