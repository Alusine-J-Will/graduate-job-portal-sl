<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');
$conn = $GLOBALS['conn'];
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

$graduateId = getGraduateIdByUserId($conn, $userId);
if (!$graduateId) {
    $_SESSION['error'] = 'Please complete your graduate profile before saving jobs.';
    redirect('profile.php');
}

$jobId = $_POST['job_id'] ?? $_GET['job_id'] ?? null;
$action = $_POST['action'] ?? $_GET['action'] ?? 'save';
$redirectTo = $_POST['redirect_to'] ?? $_GET['redirect_to'] ?? 'jobs.php';

if (!ctype_digit((string) $jobId)) {
    $_SESSION['error'] = 'Invalid job ID.';
    redirect($redirectTo);
}

$jobId = (int) $jobId;

$jobStmt = $conn->prepare('SELECT job_id FROM jobs WHERE job_id = ? AND status = ? AND (deadline IS NULL OR deadline >= CURDATE()) LIMIT 1');
$statusOpen = 'Open';
$jobStmt->bind_param('is', $jobId, $statusOpen);
$jobStmt->execute();
$jobResult = $jobStmt->get_result();
$job = $jobResult->fetch_assoc();
$jobStmt->close();

if (!$job) {
    $_SESSION['error'] = 'The selected job could not be found.';
    redirect($redirectTo);
}

if ($action === 'remove') {
    $removeStmt = $conn->prepare('DELETE FROM saved_jobs WHERE graduate_id = ? AND job_id = ?');
    $removeStmt->bind_param('ii', $graduateId, $jobId);
    $removeStmt->execute();
    $removeStmt->close();
    $_SESSION['success'] = 'Job removed from your saved list.';
    redirect($redirectTo);
}

$conn->begin_transaction();
try {
    $saveStmt = $conn->prepare('INSERT IGNORE INTO saved_jobs (graduate_id, job_id) VALUES (?, ?)');
    $saveStmt->bind_param('ii', $graduateId, $jobId);
    $saveStmt->execute();
    $saveStmt->close();
    $conn->commit();

    $_SESSION['success'] = 'Job saved successfully.';
    redirect($redirectTo);
} catch (Exception $e) {
    $conn->rollback();
    error_log('Save job failed: ' . $e->getMessage());
    $_SESSION['error'] = 'Unable to save the job at this time.';
    redirect($redirectTo);
}
