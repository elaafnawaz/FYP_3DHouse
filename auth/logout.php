<?php
// auth/logout.php
require_once __DIR__ . '/../includes/config.php';

// Clear all session variables
$_SESSION = [];

// If a session cookie exists, remove it properly using the same params
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    // Delete the session cookie by setting expiration in the past
    setcookie(session_name(), '', time() - 42000,
        $params["path"] ?? '/', 
        $params["domain"] ?? '', 
        $params["secure"] ?? false, 
        $params["httponly"] ?? true
    );
}

// Also remove our "remember me" cookie (if present)
if (!empty($_COOKIE['remember_user'])) {
    // path should match how the cookie was set ('/')
    setcookie('remember_user', '', time() - 42000, '/', '', false, true);
    unset($_COOKIE['remember_user']);
}

// Finally destroy the session
session_destroy();

// Redirect to home
header('Location: ' . BASE_URL);
exit;
