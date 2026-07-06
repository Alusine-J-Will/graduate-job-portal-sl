<?php
/**
 * Session management helper.
 *
 * Starts a secure session if none exists and regenerates session IDs when appropriate.
 */

if (session_status() === PHP_SESSION_NONE) {
    $secure = false;
    $httponly = true;
    $samesite = 'Lax';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite,
    ]);

    session_start();
}

if (empty($_SESSION['session_initialized'])) {
    session_regenerate_id(true);
    $_SESSION['session_initialized'] = true;
}
