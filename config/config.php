<?php
session_start();

define('APP_NAME', 'TokoKu');

$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
$baseUrl = str_replace('\\', '/', $scriptDir);
if ($baseUrl === '.' || $baseUrl === '') {
    $baseUrl = '/';
} elseif ($baseUrl !== '/') {
    $baseUrl = rtrim($baseUrl, '/') . '/';
}
define('BASE_URL', $baseUrl);
define('APP_ASSET_VERSION', '2026-09-21-1');

function is_secure_request(): bool {
    if (isset($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }

    if (!empty($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) {
        return true;
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        return strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https';
    }

    return false;
}

function redirect_to_https_if_needed(): void {
    if (php_sapi_name() === 'cli') {
        return;
    }

    $host = $_SERVER['HTTP_HOST'] ?? '';
    $isLocalHost = $host === '' || preg_match('/^(localhost|127\.0\.0\.1|::1|\[::1\])(:\d+)?$/i', $host) === 1;

    if (!is_secure_request() && !$isLocalHost) {
        $protocol = 'https';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $redirectTarget = $protocol . '://' . $host . $uri;
        header('Location: ' . $redirectTarget, true, 301);
        exit;
    }
}

redirect_to_https_if_needed();

function asset_url($path) {
    $path = '/' . ltrim((string) $path, '/');
    return BASE_URL . ltrim($path, '/') . '?v=' . APP_ASSET_VERSION;
}

$host = '127.0.0.1';
$db   = 'tokoku_db';
$user = 'root';
$pass = 'root';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Koneksi database gagal. Pastikan MySQL aktif dan database tokoku_db sudah dibuat.');
}

date_default_timezone_set('Asia/Jakarta');
?>
