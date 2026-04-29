<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';

class Auth {
    private Database $db;
    public function __construct() { $this->db = new Database(); }

    public function register(string $email, string $password): bool {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->query("INSERT INTO users (email, password) VALUES (?, ?)", [$email, $hash]);
        return (bool)$stmt;
    }

    public function login(string $email, string $password): bool {
        $stmt = $this->db->query("SELECT * FROM users WHERE email = ? LIMIT 1", [$email]);
        $user = $stmt ? $stmt->fetch() : null;
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['is_admin'] = $user['is_admin'] ?? 0;
            return true;
        }
        return false;
    }

    public function logout(): void {
        session_destroy();
    }
}
