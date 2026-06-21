<?php
require_once __DIR__ . '/session.php';

if (!defined('CSRF_TTL')) {
    define('CSRF_TTL', 7200); // 2 hours
}

function csrf_regenerate(): void {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_token_issued_at'] = time();
}

function csrf_token(): string {
    $now = time();
    if (
        empty($_SESSION['csrf_token']) ||
        empty($_SESSION['csrf_token_issued_at']) ||
        ($now - (int)$_SESSION['csrf_token_issued_at']) > CSRF_TTL
    ) {
        csrf_regenerate();
    }
    return (string)$_SESSION['csrf_token'];
}

function csrf_field(string $name = 'csrf_token'): string {
    $name  = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $value = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="'.$name.'" value="'.$value.'">';
}

function csrf_verify(?string $token): bool {
    if (!isset($_SESSION['csrf_token'])) return false;
    if (!is_string($token) || $token === '') return false;
    return hash_equals((string)$_SESSION['csrf_token'], $token);
}

function csrf_verify_from_post(string $name = 'csrf_token'): bool {
    $posted = $_POST[$name] ?? null;
    return csrf_verify(is_string($posted) ? $posted : null);
}
