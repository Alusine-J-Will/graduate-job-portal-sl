<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('employer');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    redirect('edit_profile.php');
}

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$companyId = isset($_POST['company_id']) ? (int) $_POST['company_id'] : null;

$companyName = sanitizeInput($_POST['company_name'] ?? '');
$industry = sanitizeInput($_POST['industry'] ?? '');
$companySize = sanitizeInput($_POST['company_size'] ?? '');
$companyLocation = sanitizeInput($_POST['company_location'] ?? '');
$website = sanitizeInput($_POST['website'] ?? '');
$foundedYear = sanitizeInput($_POST['founded_year'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');
$jobTitle = sanitizeInput($_POST['job_title'] ?? '');
$fullName = sanitizeInput($_POST['full_name'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');

$errors = [];

if ($companyName === '') {
    $errors[] = 'Company Name is required.';
}

if ($industry === '') {
    $errors[] = 'Industry is required.';
}

if ($companyLocation === '') {
    $errors[] = 'Company Location is required.';
}

if ($fullName === '') {
    $errors[] = 'Contact Name is required.';
}

if ($phone === '') {
    $errors[] = 'Contact Phone is required.';
}

if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
    $errors[] = 'Please enter a valid website URL.';
}

if (!empty($foundedYear) && (!is_numeric($foundedYear) || $foundedYear < 1800 || $foundedYear > (int) date('Y'))) {
    $errors[] = 'Please enter a valid founded year.';
}

if (!empty($errors)) {
    $_SESSION['error'] = implode(' ', $errors);
    redirect('edit_profile.php');
}

$conn = $GLOBALS['conn'];
$conn->begin_transaction();

$companyUpdated = false;
$employerUpdated = false;
$userUpdated = false;

if ($companyId) {
    $companyStmt = $conn->prepare(
        'UPDATE companies SET company_name = ?, industry = ?, company_size = ?, location = ?, website = ?, description = ?, founded_year = ?, updated_at = NOW() WHERE company_id = ?'
    );
    $companyStmt->bind_param('ssssssii', $companyName, $industry, $companySize, $companyLocation, $website, $description, $foundedYear, $companyId);
    $companyUpdated = $companyStmt->execute();
    $companyStmt->close();
} else {
    $companyStmt = $conn->prepare(
        'INSERT INTO companies (company_name, industry, company_size, location, website, description, founded_year) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $companyStmt->bind_param('sssssss', $companyName, $industry, $companySize, $companyLocation, $website, $description, $foundedYear);
    $companyUpdated = $companyStmt->execute();
    if ($companyUpdated) {
        $companyId = $companyStmt->insert_id;
        $companyUpdated = true;
    }
    $companyStmt->close();
}

if ($companyUpdated && $companyId) {
    $employerStmt = $conn->prepare('UPDATE employers SET company_id = ?, job_title = ? WHERE user_id = ?');
    $employerStmt->bind_param('isi', $companyId, $jobTitle, $userId);
    $employerUpdated = $employerStmt->execute();
    $employerStmt->close();
}

if ($companyUpdated) {
    $userStmt = $conn->prepare('UPDATE users SET full_name = ?, phone = ?, updated_at = NOW() WHERE user_id = ?');
    $userStmt->bind_param('ssi', $fullName, $phone, $userId);
    $userUpdated = $userStmt->execute();
    $userStmt->close();
}

if ($companyUpdated && $employerUpdated && $userUpdated) {
    $conn->commit();
    $_SESSION['success'] = 'Company profile updated successfully.';
    redirect('profile.php');
}

$conn->rollback();
$_SESSION['error'] = 'Unable to update your company profile. Please try again.';
redirect('edit_profile.php');
