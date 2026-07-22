<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    redirect('upload_photo.php');
}

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$file = $_FILES['profile_photo'] ?? null;

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = 'Please choose a valid photo file.';
    redirect('upload_photo.php');
}

$uploadResult = uploadFile($file, PROFILE_UPLOAD_PATH, ['jpg', 'jpeg', 'png'], MAX_FILE_SIZE);
if (!$uploadResult['success']) {
    $_SESSION['error'] = $uploadResult['message'];
    redirect('upload_photo.php');
}

$filename = $uploadResult['path'];
$conn = $GLOBALS['conn'];
$stmt = $conn->prepare('UPDATE graduates SET profile_picture = ?, updated_at = NOW() WHERE user_id = ?');
$stmt->bind_param('si', $filename, $userId);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    refreshGraduateProfileCompletion($conn, $userId);
    $_SESSION['success'] = 'Profile photo uploaded successfully.';
    redirect('profile.php');
}

$_SESSION['error'] = 'Unable to save profile photo. Please try again.';
redirect('upload_photo.php');
