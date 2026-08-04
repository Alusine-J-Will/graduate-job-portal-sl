<?php
/**
 * Mark all notifications for the current user as read.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'notifications/index.php');
}

$userId = (int) ($_SESSION['user_id'] ?? 0);
if ($userId <= 0) {
    $_SESSION['error'] = 'Invalid user.';
    redirect(BASE_URL . 'notifications/index.php');
}

$stmt = $conn->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0');
if ($stmt === false) {
    $_SESSION['error'] = 'Database error.';
    redirect(BASE_URL . 'notifications/index.php');
}
$stmt->bind_param('i', $userId);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected > 0) {
    $_SESSION['success'] = $affected . ' notifications marked as read.';
} else {
    $_SESSION['success'] = 'No unread notifications.';
}

redirect(BASE_URL . 'notifications/index.php');
