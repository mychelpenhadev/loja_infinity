<?php
ob_start();
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

            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                echo json_encode(["status" => "error", "message" => "Este e-mail já está cadastrado."]);
                exit;
            }

            if (!empty($cpf)) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE cpf = ?");
                $stmt->execute([$cpf]);
                if ($stmt->fetch()) {
                    echo json_encode(["status" => "error", "message" => "Este CPF já está cadastrado em outra conta."]);
                    exit;
                }
            }

            if (!empty($telefone)) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE telefone = ?");
                $stmt->execute([$telefone]);
                if ($stmt->fetch()) {
                    echo json_encode(["status" => "error", "message" => "Este número de telefone já está cadastrado em outra conta."]);
                    exit;
                }
            }
                $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, cpf, telefone, email, password, role, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $cpf, $telefone, $email, $hash, $role, 1])) {
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role;
                $_SESSION['user_telefone'] = $telefone;
                $_SESSION['user_cpf'] = $cpf;
                $_SESSION['user_email'] = $email;
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
            $stmt = $pdo->prepare("SELECT id, name, email, cpf, telefone, password, role, is_verified, profile_picture FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['profile_picture'] = $user['profile_picture'];
                $_SESSION['user_telefone'] = $user['telefone'];
                $_SESSION['user_cpf'] = $user['cpf'];
                $_SESSION['user_email'] = $user['email'];
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

        $token = $data['token'] ?? $_POST['credential'] ?? '';
        $isRedirect = isset($_POST['credential']);
        if (!$token) {
            if ($isRedirect) {
                header('Location: ../login.html?error=no_token');
                exit;
            }
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
                $stmt = $pdo->prepare("SELECT id, name, email, cpf, telefone, role, profile_picture FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['profile_picture'] = $user['profile_picture'];
                    $_SESSION['user_telefone'] = $user['telefone'];
                    $_SESSION['user_cpf'] = $user['cpf'];
                    $_SESSION['user_email'] = $user['email'];
                    if ($isRedirect) {
                        $target = $user['role'] === 'admin' ? '../admin.php' : '../perfil.php';
                        header("Location: $target");
                        exit;
                    }
                    echo json_encode(["status" => "success", "message" => "Bem-vindo de volta, " . $user['name'] . "!", "role" => $user['role'], "id" => $user['id'], "profile_picture" => $user['profile_picture']]);
                } else {
                    $randomPass = bin2hex(random_bytes(8));
                    $hash = password_hash($randomPass, PASSWORD_DEFAULT);
                    $role = 'cliente';
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, profile_picture, is_verified) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $email, $hash, $role, $picture, 1]);
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_role'] = $role;
                    $_SESSION['profile_picture'] = $picture;
                    $_SESSION['user_telefone'] = null;
                    $_SESSION['user_cpf'] = null;
                    $_SESSION['user_email'] = $email;
                    if ($isRedirect) {
                        header('Location: ../perfil.php');
                        exit;
                    }
                    echo json_encode(["status" => "success", "message" => "Conta criada com sucesso pelo Google!", "role" => $role, "id" => $_SESSION['user_id'], "profile_picture" => $picture]);
                }
            } else {
                if ($isRedirect) {
                    header('Location: ../login.html?error=invalid_token');
                    exit;
                }
                echo json_encode(["status" => "error", "message" => "Token inválido ou expirado."]);
            }
        } catch(Exception $e) {
            if ($isRedirect) {
                header('Location: ../login.html?error=' . urlencode($e->getMessage()));
                exit;
            }
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
            "cpf" => $_SESSION['user_cpf'] ?? null,
            "email" => $_SESSION['user_email'] ?? null
        ]);
    }
    else {
        echo json_encode(["loggedIn" => false]);
    }
    exit;
}
if ($action === 'logout') {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
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
    // Use session values as default if not sent in POST (fixes "Email cannot be empty" bug)
    $newEmail = !empty($_POST['email']) ? $_POST['email'] : ($_SESSION['user_email'] ?? '');
    $newTelefone = !empty($_POST['telefone']) ? $_POST['telefone'] : ($_SESSION['user_telefone'] ?? '');
    $newCpf = !empty($_POST['cpf']) ? $_POST['cpf'] : ($_SESSION['user_cpf'] ?? '');
    $newPicture = $_POST['profile_picture'] ?? '';

    $userId = $_SESSION['user_id'];
    
    if (empty($newName)) {
        echo json_encode(["status" => "error", "message" => "Nome não pode estar vazio."]);
        exit;
    }
    if (empty($newEmail)) {
        echo json_encode(["status" => "error", "message" => "E-mail não pode estar vazio."]);
        exit;
    }
    
    // Check if email already exists for another user
    $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmtCheck->execute([$newEmail, $userId]);
    if ($stmtCheck->fetch()) {
        echo json_encode(["status" => "error", "message" => "Este e-mail já está em uso por outra conta."]);
        exit;
    }

    $query = "UPDATE users SET name = ?, email = ?, telefone = ?, cpf = ?";
    $params = [$newName, $newEmail, $newTelefone, $newCpf];
    
    // Update session values
    $_SESSION['user_name'] = $newName;
    $_SESSION['user_email'] = $newEmail;
    $_SESSION['user_telefone'] = $newTelefone;
    $_SESSION['user_cpf'] = $newCpf;
    if (!empty($newPicture) && strpos($newPicture, 'data:image/') === 0) {
        // Process base64 image
        list($type, $data) = explode(';', $newPicture);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);

        $extension = 'jpg';
        $filename = 'user_' . $userId . '_' . time() . '.' . $extension;
        $uploadPath = __DIR__ . '/../uploads/profile_pics/' . $filename;
        $dbPath = 'uploads/profile_pics/' . $filename;

        // Try to save file
        if (file_put_contents($uploadPath, $data)) {
            // Delete old picture if exists
            $stmt_old = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
            $stmt_old->execute([$userId]);
            $oldPic = $stmt_old->fetchColumn();
            if ($oldPic) {
                deleteFileIfInUploads($oldPic);
            }

            $query .= ", profile_picture = ?";
            $params[] = $dbPath;
            $_SESSION['profile_picture'] = $dbPath;
        } else {
            @ob_clean();
            echo json_encode(["status" => "error", "message" => "Erro ao salvar foto de perfil. Verifique as permissões da pasta uploads/profile_pics."]);
            exit;
        }
    } else if (isset($_POST['delete_photo']) && $_POST['delete_photo'] == '1') {
        // Delete photo logic
        $stmt_old = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
        $stmt_old->execute([$userId]);
        $oldPic = $stmt_old->fetchColumn();
        if ($oldPic) {
            deleteFileIfInUploads($oldPic);
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

        @ob_clean();
        echo json_encode(["status" => "success", "message" => "Perfil atualizado!"]);
    } catch(PDOException $e) {
        @ob_clean();
        echo json_encode(["status" => "error", "message" => "Erro DB: " . $e->getMessage()]);
    }
    exit;
}
if ($action === 'change_password') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "Não autorizado."]);
        exit;
    }
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $userId = $_SESSION['user_id'];
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        echo json_encode(["status" => "error", "message" => "Preencha todos os campos."]);
        exit;
    }
    if ($newPassword !== $confirmPassword) {
        echo json_encode(["status" => "error", "message" => "As senhas não conferem."]);
        exit;
    }
    if (strlen($newPassword) < 8) {
        echo json_encode(["status" => "error", "message" => "A senha deve ter pelo menos 8 caracteres."]);
        exit;
    }
    try {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            echo json_encode(["status" => "error", "message" => "Senha atual incorreta."]);
            exit;
        }
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newHash, $userId]);
        @ob_clean();
        echo json_encode(["status" => "success", "message" => "Senha alterada com sucesso!"]);
    } catch(PDOException $e) {
        @ob_clean();
        echo json_encode(["status" => "error", "message" => "Erro: " . $e->getMessage()]);
    }
    exit;
}
if ($action === 'update_security') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "Não autorizado."]);
        exit;
    }
    $newEmail = $_POST['email'] ?? '';
    $newCpf = $_POST['cpf'] ?? '';
    $newTelefone = $_POST['telefone'] ?? '';
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $userId = $_SESSION['user_id'];
    
    if (!empty($newEmail) || !empty($newCpf) || !empty($newTelefone)) {
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmtCheck->execute([$newEmail, $userId]);
        if ($stmtCheck->fetch()) {
            echo json_encode(["status" => "error", "message" => "Este e-mail já está em uso por outra conta."]);
            exit;
        }
    }
    
    if (!empty($newPassword) || !empty($currentPassword) || !empty($confirmPassword)) {
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            echo json_encode(["status" => "error", "message" => "Preencha todos os campos de senha para alterá-la."]);
            exit;
        }
        if ($newPassword !== $confirmPassword) {
            echo json_encode(["status" => "error", "message" => "As senhas não conferem."]);
            exit;
        }
        if (strlen($newPassword) < 8) {
            echo json_encode(["status" => "error", "message" => "A senha deve ter pelo menos 8 caracteres."]);
            exit;
        }
        try {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                echo json_encode(["status" => "error", "message" => "Senha atual incorreta."]);
                exit;
            }
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$newHash, $userId]);
        } catch(PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Erro ao alterar senha: " . $e->getMessage()]);
            exit;
        }
    }
    
    if (!empty($newEmail) || !empty($newCpf) || !empty($newTelefone)) {
        try {
            $query = "UPDATE users SET";
            $params = [];
            if (!empty($newEmail)) {
                $query .= " email = ?,";
                $params[] = $newEmail;
                $_SESSION['user_email'] = $newEmail;
            }
            if (!empty($newCpf)) {
                $query .= " cpf = ?,";
                $params[] = $newCpf;
                $_SESSION['user_cpf'] = $newCpf;
            }
            if (!empty($newTelefone)) {
                $query .= " telefone = ?,";
                $params[] = $newTelefone;
                $_SESSION['user_telefone'] = $newTelefone;
            }
            $query = rtrim($query, ',');
            $query .= " WHERE id = ?";
            $params[] = $userId;
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            @ob_clean();
            echo json_encode(["status" => "success", "message" => "Dados atualizados com sucesso!"]);
        } catch(PDOException $e) {
            @ob_clean();
            echo json_encode(["status" => "error", "message" => "Erro ao atualizar dados: " . $e->getMessage()]);
        }
    } else {
        @ob_clean();
        echo json_encode(["status" => "success", "message" => "Senha alterada com sucesso!"]);
    }
    exit;
}
if (empty($action)) {
    echo json_encode(["status" => "error", "message" => "Ação não informada."]);
} else {
    echo json_encode(["status" => "error", "message" => "Ação '$action' inválida para este endpoint."]);
}
