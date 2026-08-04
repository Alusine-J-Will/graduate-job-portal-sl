<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please sign in with an administrator account.';
    redirect(BASE_URL . 'auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('employers.php');
}

$userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

if ($userId <= 0 || !in_array($status, ['active', 'inactive'], true)) {
    $_SESSION['error'] = 'Invalid employer status update request.';
    redirect('employers.php');
}

$conn = $GLOBALS['conn'];
$currentStatusStmt = $conn->prepare('SELECT status FROM users WHERE user_id = ? AND role = ? LIMIT 1');
$role = 'employer';
$currentStatusStmt->bind_param('is', $userId, $role);
$currentStatusStmt->execute();
$currentStatusStmt->bind_result($existingStatus);
$currentStatusStmt->fetch();
$currentStatusStmt->close();

$updateStmt = $conn->prepare('UPDATE users SET status = ? WHERE user_id = ? AND role = ?');
$updateStmt->bind_param('sis', $status, $userId, $role);
$updateStmt->execute();
$updateStmt->close();

if ($conn->affected_rows > 0) {
    if ($existingStatus !== $status) {
        $notification = generateNotification(
            $userId,
            'account',
            'Account Status Updated',
            'Your employer account status has been updated by the administrator.',
            BASE_URL . 'notifications/index.php'
        );
        saveNotification($notification);
    }

    $_SESSION['success'] = 'Employer account status updated successfully.';
} else {
    $_SESSION['error'] = 'No changes were made.';
}

redirect('employers.php');
