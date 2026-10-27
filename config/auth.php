<?php
require_once __DIR__ . '/config.php';

function is_logged_in(): bool {
    return isset($_SESSION['user']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function is_admin(): bool {
    return is_logged_in() && (current_user()['role'] ?? '') === 'admin';
}

function require_admin(): void {
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        exit('Akses ditolak. Halaman ini hanya untuk Super Admin.');
    }
}

function current_user(): array {
    return $_SESSION['user'] ?? ['name' => 'Guest', 'role' => ''];
}

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function rupiah(float|int $value): string {
    return 'Rp ' . number_format((float)$value, 0, ',', '.');
}
?>
