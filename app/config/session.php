<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

function requireAdmin(): void {
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        http_response_code(403);
        exit('Acceso denegado');
    }
}
