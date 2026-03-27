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
    public function open(string $path, string $name): bool {
        return true;
    }
    public function close(): bool {
        return true;
    }
    public function read(string $id): string|false {
        try {
            $stmt = $this->pdo->prepare("SELECT data FROM sessions WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['data'] : '';
        } catch (Exception $e) { return ''; }
    }
    public function write(string $id, string $data): bool {
        try {
            $stmt = $this->pdo->prepare("REPLACE INTO sessions (id, data, last_access) VALUES (?, ?, ?)");
            return $stmt->execute([$id, $data, time()]);
        } catch (Exception $e) { return false; }
    }
    public function destroy(string $id): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) { return false; }
    }
    public function gc(int $maxLifetime): int|false {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE last_access < ?");
            $stmt->execute([time() - $maxLifetime]);
            return $stmt->rowCount();
        } catch (Exception $e) { return 0; }
    }
}
?>
