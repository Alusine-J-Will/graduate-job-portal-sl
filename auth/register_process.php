<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request.';
    redirect('graduate_register.php');
}

$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$termsAccepted = !empty($_POST['terms']);

$errors = [];

if ($firstName === '') {
    $errors[] = 'First name is required.';
}

if ($lastName === '') {
    $errors[] = 'Last name is required.';
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if ($phone === '' || !preg_match('/^\+?[0-9\s\-()]{7,15}$/', $phone)) {
    $errors[] = 'Please enter a valid phone number.';
}

if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters long.';
}

if (!preg_match('/[A-Z]/', $password)) {
    $errors[] = 'Password must include an uppercase letter.';
}

if (!preg_match('/[a-z]/', $password)) {
    $errors[] = 'Password must include a lowercase letter.';
}

if (!preg_match('/\d/', $password)) {
    $errors[] = 'Password must include a number.';
}

if (!preg_match('/[^A-Za-z0-9]/', $password)) {
    $errors[] = 'Password must include a special character.';
}

if ($password !== $confirmPassword) {
    $errors[] = 'Passwords do not match.';
}

if (!$termsAccepted) {
    $errors[] = 'You must accept the terms and conditions.';
}

if (!empty($errors)) {
    $_SESSION['error'] = implode(' ', $errors);
    redirect('graduate_register.php');
}

$conn = $GLOBALS['conn'];

$stmt = $conn->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt->close();
    $_SESSION['error'] = 'Email already exists.';
    redirect('graduate_register.php');
}

$stmt->close();

$fullName = trim($firstName . ' ' . $lastName);
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$createdAt = date('Y-m-d H:i:s');

$conn->begin_transaction();

try {
    $userStmt = $conn->prepare('INSERT INTO users (full_name, email, phone, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $role = 'graduate';
    $status = 'pending';
    $userStmt->bind_param('sssssss', $fullName, $email, $phone, $hashedPassword, $role, $status, $createdAt);
    $userStmt->execute();

    if ($userStmt->affected_rows !== 1) {
        throw new Exception('User insert did not create a record.');
    }

    $userId = $userStmt->insert_id;
    $userStmt->close();

    $graduateStmt = $conn->prepare('INSERT INTO graduates (user_id, created_at) VALUES (?, ?)');
    $graduateStmt->bind_param('is', $userId, $createdAt);
    $graduateStmt->execute();

    if ($graduateStmt->affected_rows !== 1) {
        throw new Exception('Graduate insert did not create a record.');
    }

    $graduateStmt->close();
    $conn->commit();

    $_SESSION['success'] = 'Registration successful. Please login to continue.';
    redirect('login.php');
} catch (Exception $e) {
    $conn->rollback();
    error_log('Graduate registration failed: ' . $e->getMessage());
    $_SESSION['error'] = 'Registration failed. Please try again later.';
    redirect('graduate_register.php');
}
