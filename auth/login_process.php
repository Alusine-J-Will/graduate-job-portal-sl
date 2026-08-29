<?php
require_once '../config/config.php';
require_once '../includes/functions.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request.';
    redirect('login.php');
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if ($password === '') {
    $errors[] = 'Please enter your password.';
}

if (!empty($errors)) {
    $_SESSION['error'] = implode(' ', $errors);
    redirect('login.php');
}

$conn = $GLOBALS['conn'];

$stmt = $conn->prepare('SELECT user_id, full_name, email, password, role, status, email_verified FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    $_SESSION['error'] = 'Invalid email or password.';
    redirect('login.php');
}

$user = $result->fetch_assoc();
$stmt->close();

if (!password_verify($password, $user['password'])) {
    $_SESSION['error'] = 'Invalid email or password.';
    redirect('login.php');
}

if ($user['role'] !== 'admin' && (int) ($user['email_verified'] ?? 0) !== 1) {
    $_SESSION['warning'] = 'Please verify your email address before logging in.';
    redirect('login.php');
}

if ($user['status'] === 'pending') {
    $_SESSION['warning'] = 'Your account is awaiting activation.';
    redirect('login.php');
}

if ($user['status'] === 'inactive') {
    $_SESSION['error'] = 'Your account has been deactivated. Please contact support.';
    redirect('login.php');
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user['user_id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];

switch ($user['role']) {
    case 'graduate':
        redirect('../graduate/dashboard.php');
        break;
    case 'employer':
        redirect('../employer/dashboard.php');
        break;
    case 'admin':
        redirect('../admin/dashboard.php');
        break;
    default:
        $_SESSION['error'] = 'Unexpected server error.';
        redirect('login.php');
}
