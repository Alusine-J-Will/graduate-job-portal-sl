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

// Role-based authorization checks will be added here in later development.
