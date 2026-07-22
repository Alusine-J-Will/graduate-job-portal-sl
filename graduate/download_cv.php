<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');

if (!isset($_GET['file']) || empty($_GET['file'])) {
    $_SESSION['error'] = 'Missing CV file.';
    redirect('profile.php');
}

$fileName = basename($_GET['file']);
$filePath = CV_UPLOAD_PATH . $fileName;

if (!file_exists($filePath) || !is_readable($filePath)) {
    $_SESSION['error'] = 'CV file not found.';
    redirect('profile.php');
}

$mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
header('Content-Description: File Transfer');
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
