<?php

class DatabaseSessionHandler implements SessionHandlerInterface {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;

        try {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS sessions (
                id VARCHAR(128) NOT NULL PRIMARY KEY,
                data TEXT NOT NULL,
                last_access INT(11) NOT NULL,
                INDEX idx_last_access (last_access)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        } catch (Exception $e) {

        }
    }
    public function open($savePath, $sessionName) {
        return true;
    }
    public function close() {
        return true;
    }
    public function read($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT data FROM sessions WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['data'] : '';
        } catch (Exception $e) { return ''; }
    }
    public function write($id, $data) {
        try {
            $stmt = $this->pdo->prepare("REPLACE INTO sessions (id, data, last_access) VALUES (?, ?, ?)");
            return $stmt->execute([$id, $data, time()]);
        } catch (Exception $e) { return false; }
    }
    public function destroy($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) { return false; }
    }
    public function gc($maxLifetime) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE last_access < ?");
            $stmt->execute([time() - $maxLifetime]);
            return true;
        } catch (Exception $e) { return 0; }
    }
}

$session_lifetime = 30 * 24 * 60 * 60;
ini_set('session.gc_maxlifetime', $session_lifetime);
ini_set('session.cookie_lifetime', $session_lifetime);

require_once 'db.php';
$handler = new DatabaseSessionHandler($pdo);
session_set_save_handler($handler, true);
?>
