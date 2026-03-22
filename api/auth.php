<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');
$action = $_GET['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if ($action === 'register') {
        $name = isset($data['name']) ? trim($data['name']) : trim($_POST['name'] ?? '');
        $cpf = isset($data['cpf']) ? trim($data['cpf']) : trim($_POST['cpf'] ?? '');
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
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, cpf, email, password, role) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $cpf, $email, $hash, $role])) {
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role;
                echo json_encode(["status" => "success", "message" => "Conta criada com sucesso."]);
            }
            else {
                echo json_encode(["status" => "error", "message" => "Ocorreu um erro ao salvar o registro."]);
            }
        }
        catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Erro de Banco de Dados."]);
        }
        exit;
    }
    if ($action === 'login') {
        $email = isset($data['email']) ? trim($data['email']) : trim($_POST['email'] ?? '');
        $password = isset($data['password']) ? $data['password'] : ($_POST['password'] ?? '');
        try {
            $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                echo json_encode(["status" => "success", "message" => "Login efetuado com sucesso.", "role" => $user['role'], "id" => $user['id']]);
            }
            else {
                echo json_encode(["status" => "error", "message" => "E-mail ou senha incorretos."]);
            }
        }
        catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Erro de Banco de Dados."]);
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
            "role" => $_SESSION['user_role']
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
    $userId = $_SESSION['user_id'];
    
    if (empty($newName)) {
        echo json_encode(["status" => "error", "message" => "Nome não pode estar vazio."]);
        exit;
    }
    
    if (!empty($newPassword)) {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET name = ?, password = ? WHERE id = ?");
        $stmt->execute([$newName, $hashed, $userId]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->execute([$newName, $userId]);
    }
    
    $_SESSION['user_name'] = $newName;
    
    echo json_encode(["status" => "success", "message" => "Perfil atualizado!"]);
    exit;
}

echo json_encode(["status" => "error", "message" => "Ação inválida."]);
?>
