<?php
/**
 * Mark a single notification as read.
 * Accepts POST: notification_id
 * Responds with JSON for XHR or redirects back with flash messages.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'notifications/index.php');
}

$userId = (int) ($_SESSION['user_id'] ?? 0);
$notificationId = isset($_POST['notification_id']) ? (int) $_POST['notification_id'] : 0;

if ($userId <= 0 || $notificationId <= 0) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    $_SESSION['error'] = 'Invalid notification.';
    redirect(BASE_URL . 'notifications/index.php');
}

$stmt = $conn->prepare('UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ? LIMIT 1');
if ($stmt === false) {
    $_SESSION['error'] = 'Database error';
    redirect(BASE_URL . 'notifications/index.php');
}
$stmt->bind_param('ii', $notificationId, $userId);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['success' => $affected > 0, 'updated' => $affected]);
    exit;
}

if ($affected > 0) {
    $_SESSION['success'] = 'Notification marked as read.';
} else {
    $_SESSION['error'] = 'Notification not found or already read.';
}

redirect(BASE_URL . 'notifications/index.php');
