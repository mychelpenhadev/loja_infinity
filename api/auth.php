require_once 'security.php';
header('Content-Type: application/json');
ob_clean();
$action = $_GET['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if ($action === 'register') {
        $name = isset($data['name']) ? trim($data['name']) : trim($_POST['name'] ?? '');
        $cpf = isset($data['cpf']) ? trim($data['cpf']) : trim($_POST['cpf'] ?? '');
        $telefone = isset($data['telefone']) ? trim($data['telefone']) : trim($_POST['telefone'] ?? '');
        $email = isset($data['email']) ? trim($data['email']) : trim($_POST['email'] ?? '');
        $password = isset($data['password']) ? $data['password'] : ($_POST['password'] ?? '');
        $role = 'cliente';
        if (empty($name) || empty($email) || empty($password)) {
            echo json_encode(["status" => "error", "message" => "Preencha todos os campos."]);
            exit;
        }
        try {
            // 1. Verificar Email
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                echo json_encode(["status" => "error", "message" => "Este e-mail já está cadastrado."]);
                exit;
            }

            // 2. Verificar CPF (se fornecido)
            if (!empty($cpf)) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE cpf = ?");
                $stmt->execute([$cpf]);
                if ($stmt->fetch()) {
                    echo json_encode(["status" => "error", "message" => "Este CPF já está cadastrado em outra conta."]);
                    exit;
                }
            }

            // 3. Verificar Telefone (se fornecido)
            if (!empty($telefone)) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE telefone = ?");
                $stmt->execute([$telefone]);
                if ($stmt->fetch()) {
                    echo json_encode(["status" => "error", "message" => "Este número de telefone já está cadastrado em outra conta."]);
                    exit;
                }
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, cpf, telefone, email, password, role) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $cpf, $telefone, $email, $hash, $role])) {
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role;
                $_SESSION['user_telefone'] = $telefone;
                $_SESSION['user_cpf'] = $cpf;
                echo json_encode(["status" => "success", "message" => "Conta criada com sucesso."]);
            }
            else {
                echo json_encode(["status" => "error", "message" => "Ocorreu um erro ao salvar o registro."]);
            }
        }
        catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Erro SQL: " . $e->getMessage()]);
        }
        exit;
    }
    if ($action === 'login') {
        $email = isset($data['email']) ? trim($data['email']) : trim($_POST['email'] ?? '');
        $password = isset($data['password']) ? $data['password'] : ($_POST['password'] ?? '');
        try {
            $stmt = $pdo->prepare("SELECT id, name, cpf, telefone, password, role, profile_picture FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['profile_picture'] = $user['profile_picture'];
                $_SESSION['user_telefone'] = $user['telefone'];
                $_SESSION['user_cpf'] = $user['cpf'];
                echo json_encode(["status" => "success", "message" => "Login efetuado com sucesso.", "role" => $user['role'], "id" => $user['id'], "profile_picture" => $user['profile_picture']]);
            }
            else {
                echo json_encode(["status" => "error", "message" => "E-mail ou senha incorretos."]);
            }
        }
        catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Erro SQL: " . $e->getMessage()]);
        }
        exit;
    }
    
    if ($action === 'google_login') {
        $token = $data['token'] ?? '';
        if (!$token) {
            echo json_encode(["status" => "error", "message" => "Autenticação falhou."]);
            exit;
        }
        
        try {
            require_once __DIR__ . '/../vendor/autoload.php';
            $client = new Google_Client(['client_id' => '375279591438-7uirtbvgbtsd2c2pjti9kmmhal8r2sr3.apps.googleusercontent.com']);
            $payload = $client->verifyIdToken($token);
            if ($payload) {
                $email = $payload['email'];
                $name = $payload['name'] ?? 'Usuário Google';
                $picture = $payload['picture'] ?? null;
                
                $stmt = $pdo->prepare("SELECT id, name, cpf, telefone, role, profile_picture FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['profile_picture'] = $user['profile_picture'];
                    $_SESSION['user_telefone'] = $user['telefone'];
                    $_SESSION['user_cpf'] = $user['cpf'];
                    echo json_encode(["status" => "success", "message" => "Bem-vindo de volta, " . $user['name'] . "!", "role" => $user['role'], "id" => $user['id'], "profile_picture" => $user['profile_picture']]);
                } else {
                    $randomPass = bin2hex(random_bytes(8));
                    $hash = password_hash($randomPass, PASSWORD_DEFAULT);
                    $role = 'cliente';
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, profile_picture) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $email, $hash, $role, $picture]);
                    
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_role'] = $role;
                    $_SESSION['profile_picture'] = $picture;
                    $_SESSION['user_telefone'] = null;
                    $_SESSION['user_cpf'] = null;
                    echo json_encode(["status" => "success", "message" => "Conta criada com sucesso pelo Google!", "role" => $role, "id" => $_SESSION['user_id'], "profile_picture" => $picture]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Token inválido ou expirado."]);
            }
        } catch(Exception $e) {
            echo json_encode(["status" => "error", "message" => "Erro na verificação do Google: " . $e->getMessage()]);
        }
        exit;
    }
}
if ($action === 'check') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            "loggedIn" => true,
            "id" => $_SESSION['user_id'],
            "name" => $_SESSION['user_name'],
            "role" => $_SESSION['user_role'],
            "profile_picture" => $_SESSION['profile_picture'] ?? null,
            "telefone" => $_SESSION['user_telefone'] ?? null,
            "cpf" => $_SESSION['user_cpf'] ?? null
        ]);
    }
    else {
        echo json_encode(["loggedIn" => false]);
    }
    exit;
}
if ($action === 'logout') {
    session_destroy();
    echo json_encode(["status" => "success"]);
    exit;
}

if ($action === 'update_profile') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "Não autorizado."]);
        exit;
    }
    
    $newName = $_POST['name'] ?? '';
    $newPassword = $_POST['password'] ?? '';
    $newPicture = $_POST['profile_picture'] ?? '';
    $newTelefone = $_POST['telefone'] ?? '';
    $newCpf = $_POST['cpf'] ?? '';
    $userId = $_SESSION['user_id'];
    
    if (empty($newName)) {
        echo json_encode(["status" => "error", "message" => "Nome não pode estar vazio."]);
        exit;
    }
    
    $query = "UPDATE users SET name = ?, telefone = ?, cpf = ?";
    $params = [$newName, $newTelefone, $newCpf];
    
    if (!empty($newPassword)) {
        $query .= ", password = ?";
        $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
    }
    
    if (!empty($newPicture) && strpos($newPicture, 'data:image/') === 0) {
        // Decode base64
        list($type, $data) = explode(';', $newPicture);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);
        
        // Create filename
        $extension = 'jpg'; // We forced jpeg in JS
        $filename = 'user_' . $userId . '_' . time() . '.' . $extension;
        $uploadPath = __DIR__ . '/../uploads/profile_pics/' . $filename;
        $dbPath = 'uploads/profile_pics/' . $filename;
        
        // Remove old file if exists
        $stmt_old = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
        $stmt_old->execute([$userId]);
        $oldPic = $stmt_old->fetchColumn();
        if ($oldPic && strpos($oldPic, 'uploads/') === 0 && file_exists(__DIR__ . '/../' . $oldPic)) {
            @unlink(__DIR__ . '/../' . $oldPic);
        }

        if (file_put_contents($uploadPath, $data)) {
            $query .= ", profile_picture = ?";
            $params[] = $dbPath;
            $_SESSION['profile_picture'] = $dbPath;
        }
    } else if (isset($_POST['delete_photo']) && $_POST['delete_photo'] == '1') {
        // Remove old file if exists
        $stmt_old = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
        $stmt_old->execute([$userId]);
        $oldPic = $stmt_old->fetchColumn();
        if ($oldPic && strpos($oldPic, 'uploads/') === 0 && file_exists(__DIR__ . '/../' . $oldPic)) {
            @unlink(__DIR__ . '/../' . $oldPic);
        }
        
        $query .= ", profile_picture = NULL";
        $_SESSION['profile_picture'] = null;
    }
    
    $query .= " WHERE id = ?";
    $params[] = $userId;
    
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        $_SESSION['user_name'] = $newName;
        $_SESSION['user_telefone'] = $newTelefone;
        $_SESSION['user_cpf'] = $newCpf;
        
        // Ensure no other text was outputted
        @ob_clean();
        echo json_encode(["status" => "success", "message" => "Perfil atualizado!"]);
    } catch(PDOException $e) {
        @ob_clean();
        echo json_encode(["status" => "error", "message" => "Erro DB: " . $e->getMessage()]);
    }
    exit;
}


if (empty($action)) {
    echo json_encode(["status" => "error", "message" => "Ação não informada."]);
} else {
    echo json_encode(["status" => "error", "message" => "Ação '$action' inválida para este endpoint."]);
}
