<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please sign in with an administrator account.';
    redirect(BASE_URL . 'auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('applications.php');
}

$applicationId = isset($_POST['application_id']) ? (int) $_POST['application_id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

if ($applicationId <= 0 || !in_array($status, ['Pending', 'Reviewed', 'Shortlisted', 'Rejected', 'Accepted'], true)) {
    $_SESSION['error'] = 'Invalid application status request.';
    redirect('applications.php');
}

$conn = $GLOBALS['conn'];
$checkStmt = $conn->prepare('SELECT application_id FROM applications WHERE application_id = ? LIMIT 1');
$checkStmt->bind_param('i', $applicationId);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows === 0) {
    $checkStmt->close();
    $_SESSION['error'] = 'Application not found.';
    redirect('applications.php');
}
$checkStmt->close();

$updateStmt = $conn->prepare('UPDATE applications SET status = ?, updated_at = NOW() WHERE application_id = ?');
$updateStmt->bind_param('si', $status, $applicationId);
$updateStmt->execute();
$updateStmt->close();

if ($conn->affected_rows > 0) {
    $_SESSION['success'] = 'Application status updated successfully.';
} else {
    $_SESSION['error'] = 'No changes were made.';
}

redirect('applications.php');
