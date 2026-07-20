<?php
/**
 * Authentication guard.
 *
 * Redirects unauthenticated users to the login page.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . 'auth/login.php');
}

/**
 * Require a specific user role before allowing access.
 *
 * @param string $role
 * @return void
 */
function requireRole(string $role): void
{
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        redirect(BASE_URL . 'auth/login.php');
    }
}
