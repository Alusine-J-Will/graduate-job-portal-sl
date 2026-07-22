<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    redirect('upload_cv.php');
}

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$file = $_FILES['cv_file'] ?? null;

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = 'Please choose a valid CV file.';
    redirect('upload_cv.php');
}

$uploadResult = uploadFile($file, CV_UPLOAD_PATH, ['pdf', 'doc', 'docx'], MAX_FILE_SIZE);
if (!$uploadResult['success']) {
    $_SESSION['error'] = $uploadResult['message'];
    redirect('upload_cv.php');
}

$filename = $uploadResult['path'];
$conn = $GLOBALS['conn'];
$stmt = $conn->prepare('UPDATE graduates SET cv = ?, updated_at = NOW() WHERE user_id = ?');
$stmt->bind_param('si', $filename, $userId);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    refreshGraduateProfileCompletion($conn, $userId);
    $_SESSION['success'] = 'CV uploaded successfully.';
    redirect('profile.php');
}

$_SESSION['error'] = 'Unable to save CV. Please try again.';
redirect('upload_cv.php');
