<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please sign in with an administrator account.';
    redirect(BASE_URL . 'auth/login.php');
}

$_SESSION['error'] = 'You do not have permission to modify jobs.';
redirect('jobs.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('jobs.php');
}

$jobId = isset($_POST['job_id']) ? (int) $_POST['job_id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

if ($jobId <= 0 || !in_array($status, ['Open', 'Closed', 'Draft'], true)) {
    $_SESSION['error'] = 'Invalid job status update request.';
    redirect('jobs.php');
}

$conn = $GLOBALS['conn'];
$checkStmt = $conn->prepare('SELECT job_id FROM jobs WHERE job_id = ? LIMIT 1');
$checkStmt->bind_param('i', $jobId);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows === 0) {
    $checkStmt->close();
    $_SESSION['error'] = 'Job not found.';
    redirect('jobs.php');
}
$checkStmt->close();

$updateStmt = $conn->prepare('UPDATE jobs SET status = ? WHERE job_id = ?');
$updateStmt->bind_param('si', $status, $jobId);
$updateStmt->execute();
$updateStmt->close();

if ($conn->affected_rows > 0) {
    $_SESSION['success'] = 'Job status updated successfully.';
} else {
    $_SESSION['error'] = 'No changes were made.';
}

redirect('jobs.php');
