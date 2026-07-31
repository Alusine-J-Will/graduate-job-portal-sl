<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('employer');
$conn = $GLOBALS['conn'];
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('applicants.php');
}

$applicationId = isset($_POST['application_id']) ? (int) $_POST['application_id'] : 0;
$status = trim($_POST['status'] ?? '');
$allowedStatuses = ['Pending', 'Under Review', 'Shortlisted', 'Interview Scheduled', 'Accepted', 'Rejected'];

if ($applicationId <= 0 || !in_array($status, $allowedStatuses, true)) {
    $_SESSION['error'] = 'Invalid application status update request.';
    redirect('applicants.php');
}

$employerStmt = $conn->prepare('SELECT company_id FROM employers WHERE user_id = ? LIMIT 1');
$employerStmt->bind_param('i', $userId);
$employerStmt->execute();
$employerResult = $employerStmt->get_result();
$employer = $employerResult->fetch_assoc();
$employerStmt->close();

if (!$employer || empty($employer['company_id'])) {
    $_SESSION['error'] = 'Please complete your company profile first.';
    redirect('dashboard.php');
}

$companyId = (int) $employer['company_id'];

$applicationStmt = $conn->prepare(
    'SELECT a.application_id
     FROM applications a
     INNER JOIN jobs j ON a.job_id = j.job_id
     WHERE a.application_id = ? AND j.company_id = ?
     LIMIT 1'
);
$applicationStmt->bind_param('ii', $applicationId, $companyId);
$applicationStmt->execute();
$applicationResult = $applicationStmt->get_result();
$application = $applicationResult->fetch_assoc();
$applicationStmt->close();

if (!$application) {
    $_SESSION['error'] = 'You do not have permission to update that application.';
    redirect('applicants.php');
}

$updateStmt = $conn->prepare('UPDATE applications SET status = ?, updated_at = NOW() WHERE application_id = ?');
$updateStmt->bind_param('si', $status, $applicationId);
$updateStmt->execute();
$updateStmt->close();

$_SESSION['success'] = 'Application status updated successfully.';
redirect('application_details.php?application_id=' . $applicationId);
