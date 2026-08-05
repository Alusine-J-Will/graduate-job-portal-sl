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
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            redirect(BASE_URL . 'admin/dashboard.php');
        }

        if (isset($_SESSION['role']) && $_SESSION['role'] === 'employer') {
            redirect(BASE_URL . 'employer/dashboard.php');
        }

        if (isset($_SESSION['role']) && $_SESSION['role'] === 'graduate') {
            redirect(BASE_URL . 'graduate/dashboard.php');
        }

        redirect(BASE_URL . 'auth/login.php');
    }
}
