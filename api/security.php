<?php
require_once 'db.php';
require_once 'session_db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}
function requireAdmin() {
    if (!isAdmin()) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Acesso negado. Apenas administradores podem realizar esta ação."]);
        exit;
    }
}
