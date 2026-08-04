<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/email_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    redirect('employer_register.php');
}

$fullName = sanitizeInput($_POST['full_name'] ?? '');
$companyName = sanitizeInput($_POST['company_name'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');
$industry = sanitizeInput($_POST['industry'] ?? '');
$companySize = sanitizeInput($_POST['company_size'] ?? '');
$website = sanitizeInput($_POST['website'] ?? '');
$companyAddress = sanitizeInput($_POST['company_address'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$termsAccepted = !empty($_POST['terms']);

$errors = [];

if ($fullName === '') {
    $errors[] = 'Full Name is required.';
}

if ($companyName === '') {
    $errors[] = 'Company Name is required.';
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid business email.';
}

if ($phone === '' || !preg_match('/^\+?[0-9\s\-()]{7,20}$/', $phone)) {
    $errors[] = 'Please enter a valid phone number.';
}

if ($industry === '') {
    $errors[] = 'Industry is required.';
}

if ($companySize === '') {
    $errors[] = 'Company Size is required.';
}

if ($companyAddress === '') {
    $errors[] = 'Company Address is required.';
}

if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters long.';
}

if (!preg_match('/[A-Z]/', $password)) {
    $errors[] = 'Password must include at least one uppercase letter.';
}

if (!preg_match('/[a-z]/', $password)) {
    $errors[] = 'Password must include at least one lowercase letter.';
}

if (!preg_match('/\d/', $password)) {
    $errors[] = 'Password must include at least one number.';
}

if (!preg_match('/[^A-Za-z0-9]/', $password)) {
    $errors[] = 'Password must include at least one special character.';
}

if ($password !== $confirmPassword) {
    $errors[] = 'Passwords do not match.';
}

if (!$termsAccepted) {
    $errors[] = 'You must accept the terms and conditions.';
}

if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
    $errors[] = 'Please enter a valid website URL.';
}

if (!empty($errors)) {
    $_SESSION['error'] = implode(' ', $errors);
    redirect('employer_register.php');
}

$conn = $GLOBALS['conn'];

$duplicateEmailStmt = $conn->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
$duplicateEmailStmt->bind_param('s', $email);
$duplicateEmailStmt->execute();
$duplicateEmailResult = $duplicateEmailStmt->get_result();

if ($duplicateEmailResult->num_rows > 0) {
    $duplicateEmailStmt->close();
    $_SESSION['error'] = 'Email already exists. Please use a different email address.';
    redirect('employer_register.php');
}

$duplicateEmailStmt->close();

$duplicateCompanyStmt = $conn->prepare('SELECT company_id FROM companies WHERE company_name = ? LIMIT 1');
$duplicateCompanyStmt->bind_param('s', $companyName);
$duplicateCompanyStmt->execute();
$duplicateCompanyResult = $duplicateCompanyStmt->get_result();

if ($duplicateCompanyResult->num_rows > 0) {
    $duplicateCompanyStmt->close();
    $_SESSION['error'] = 'Company name already exists. Please use a different company name.';
    redirect('employer_register.php');
}

$duplicateCompanyStmt->close();

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$createdAt = date('Y-m-d H:i:s');

$conn->begin_transaction();

try {
    $userStmt = $conn->prepare(
        'INSERT INTO users (full_name, email, phone, password, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)' 
    );
    $role = 'employer';
    $status = 'active';
    $userStmt->bind_param('ssssssss', $fullName, $email, $phone, $hashedPassword, $role, $status, $createdAt, $createdAt);
    $userStmt->execute();

    if ($userStmt->affected_rows !== 1) {
        throw new Exception('Unable to create employer user.');
    }

    $userId = $userStmt->insert_id;
    $userStmt->close();

    $companyStmt = $conn->prepare(
        'INSERT INTO companies (company_name, industry, company_size, location, website, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)' 
    );
    $companyStmt->bind_param('sssssss', $companyName, $industry, $companySize, $companyAddress, $website, $createdAt, $createdAt);
    $companyStmt->execute();

    if ($companyStmt->affected_rows !== 1) {
        throw new Exception('Unable to create employer company.');
    }

    $companyId = $companyStmt->insert_id;
    $companyStmt->close();

    $employerStmt = $conn->prepare(
        'INSERT INTO employers (user_id, company_id, job_title, created_at) VALUES (?, ?, NULL, ?)' 
    );
    $employerStmt->bind_param('iis', $userId, $companyId, $createdAt);
    $employerStmt->execute();

    if ($employerStmt->affected_rows !== 1) {
        throw new Exception('Unable to create employer profile.');
    }

    $employerStmt->close();

    $conn->commit();
    sendApplicationEmail($email, $fullName, 'welcome', []);
    // Notify admins about new employer registration and pending company
    $notificationTitle = 'New Employer Registration';
    $notificationMessage = 'A new employer account has been registered and may require review.';
    notifyAdmins($conn, 'admin_employer', $notificationTitle, $notificationMessage, BASE_URL . 'admin/employers.php');

    // If company defaults to Pending verification (by schema default), alert admins
    $companyPendingTitle = 'Company Awaiting Approval';
    $companyPendingMessage = 'A company profile is awaiting verification review.';
    notifyAdmins($conn, 'admin_verification', $companyPendingTitle, $companyPendingMessage, BASE_URL . 'admin/employers.php');

    $_SESSION['success'] = 'Employer registration successful. Please login to continue.';
    redirect('login.php');
} catch (Exception $e) {
    $conn->rollback();
    error_log('Employer registration failed: ' . $e->getMessage());
    $_SESSION['error'] = 'Registration failed. Please try again later.';
    redirect('employer_register.php');
}
