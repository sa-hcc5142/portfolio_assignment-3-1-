<?php
// app/auth.php
// Authentication & authorization helpers for the portfolio site.
// Depends on: app/session.php (starts session), app/config.php (APP_URL), app/db.php (db())

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// --- Internal util: base URL (uses APP_URL if defined) ---
if (!function_exists('auth_base_url')) {
    function auth_base_url(): string {
        // Prefer $APP_URL defined in config.php
        if (isset($GLOBALS['APP_URL']) && is_string($GLOBALS['APP_URL']) && $GLOBALS['APP_URL'] !== '') {
            return rtrim($GLOBALS['APP_URL'], '/');
        }
        // Fallback (adjust if your folder name differs)
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http';
        return $scheme . '://' . $host . '/portfolio';
    }
}

// --- Internal util: safe redirect ---
if (!function_exists('auth_redirect')) {
    function auth_redirect(string $pathOrUrl): void {
        // If it's a full URL, redirect directly. Otherwise treat as path relative to APP_URL.
        $isFull = (bool)preg_match('~^https?://~i', $pathOrUrl);
        $target = $isFull ? $pathOrUrl : auth_base_url() . '/' . ltrim($pathOrUrl, '/');
        header('Location: ' . $target);
        exit;
    }
}

// --- Session helpers ---
if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool {
        return !empty($_SESSION['user_id']);
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool {
        // Prefer cached role in session; if absent, try to load user
        if (!empty($_SESSION['role'])) {
            return $_SESSION['role'] === 'ADMIN';
        }
        $u = current_user();
        return isset($u['role']) && $u['role'] === 'ADMIN';
    }
}

if (!function_exists('current_user')) {
    function current_user(): ?array {
        if (!is_logged_in()) return null;

        // If we cached the user payload, return it
        if (!empty($_SESSION['user_cache']) && is_array($_SESSION['user_cache'])) {
            return $_SESSION['user_cache'];
        }

        // Otherwise fetch from DB
        $conn = db();
        $id = (int)$_SESSION['user_id'];
        $sql = "SELECT id, name, email, role, created_at, last_login_at
                FROM users
                WHERE id = ?
                LIMIT 1";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : null;
            mysqli_stmt_close($stmt);

            if ($row) {
                $_SESSION['user_cache'] = $row;
                // keep role mirrored at root for quick checks
                $_SESSION['role'] = $row['role'] ?? null;
                return $row;
            }
        }
        // User not found (deleted?), force logout
        logout_user();
        return null;
    }
}

// --- Route guards ---
if (!function_exists('require_login')) {
    function require_login(): void {
        if (!is_logged_in()) {
            // Send to login page; you can add a "next" if you want
            auth_redirect('public/login.php');
        }
    }
}

if (!function_exists('require_admin')) {
    function require_admin(): void {
        if (!is_admin()) {
            // Non-admins back to home section
            auth_redirect('#home');
        }
    }
}

// --- Auth actions ---
if (!function_exists('login_user')) {
    /**
     * Authenticate a user by email & password.
     * On success: sets session, updates last_login_at, sets last_login_at cookie.
     * @return bool True on success, false on failure.
     */
    function login_user(string $email, string $password, ?string $redirectTo = null): bool {
        $email = trim(mb_strtolower($email));
        if ($email === '' || $password === '') {
            return false;
        }

        $conn = db();
        $sql = "SELECT id, name, email, password_hash, role
                FROM users
                WHERE email = ? AND role = 'ADMIN'
                LIMIT 1";
        if (!($stmt = mysqli_prepare($conn, $sql))) {
            return false;
        }
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $user = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);

        if (!$user) {
            return false; // no such email
        }
        if (!password_verify($password, $user['password_hash'])) {
            return false; // wrong password
        }   // NEW: Only allow ADMIN to log in
         if (($user['role'] ?? '') !== 'ADMIN') {
             return false;
       }

        // Auth OK → set session
        $_SESSION['user_id']    = (int)$user['id'];
        $_SESSION['role']       = $user['role'] ?? null;
        $_SESSION['user_cache'] = [
            'id'    => (int)$user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];

        // Update last_login_at
        if ($upd = mysqli_prepare($conn, "UPDATE users SET last_login_at = NOW() WHERE id = ?")) {
            mysqli_stmt_bind_param($upd, "i", $_SESSION['user_id']);
            mysqli_stmt_execute($upd);
            mysqli_stmt_close($upd);
        }

        // Optional cookie: last_login_at for footer or UX
        setcookie('last_login_at', date('c'), time() + 86400 * 365, '/');

        if ($redirectTo !== null) {
            auth_redirect($redirectTo);
        }
        return true;
    }
}

if (!function_exists('logout_user')) {
    /**
     * Clear the session and optionally redirect.
     */
    function logout_user(?string $redirectTo = null): void {
        // Clear user cache and role
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        // Expire last_login cookie
        setcookie('last_login_at', '', time() - 3600, '/');

        // Start a fresh session to avoid issues in pages that expect a session
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if ($redirectTo !== null) {
            auth_redirect($redirectTo);
        }
    }
}
