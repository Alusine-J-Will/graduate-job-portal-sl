<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request.';
    redirect('register.php');
}

$role = strtolower(trim($_POST['role'] ?? 'graduate'));

if ($role === 'employer') {
    require_once __DIR__ . '/employer_register_process.php';
    exit;
}

require_once __DIR__ . '/graduate_register_process.php';
exit;
