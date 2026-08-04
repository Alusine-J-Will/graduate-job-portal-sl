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
$verificationStatus = isset($_POST['verification_status']) ? trim($_POST['verification_status']) : '';

if ($userId <= 0 || !in_array($verificationStatus, ['approved', 'pending', 'rejected'], true)) {
    $_SESSION['error'] = 'Invalid company verification request.';
    redirect('employers.php');
}

$conn = $GLOBALS['conn'];
$companyStmt = $conn->prepare('SELECT company_id FROM employers WHERE user_id = ? LIMIT 1');
$companyStmt->bind_param('i', $userId);
$companyStmt->execute();
$companyResult = $companyStmt->get_result();
$company = $companyResult->fetch_assoc();
$companyStmt->close();

if (!$company || empty($company['company_id'])) {
    $_SESSION['error'] = 'No company profile found for this employer.';
    redirect('employers.php');
}

$companyId = (int) $company['company_id'];
$verificationValue = ucfirst($verificationStatus);
$updateStmt = $conn->prepare('UPDATE companies SET verification_status = ? WHERE company_id = ?');
$updateStmt->bind_param('si', $verificationValue, $companyId);
$updateStmt->execute();
$updateStmt->close();

if ($conn->affected_rows > 0) {
    $notificationMap = [
        'Approved' => [
            'type' => 'verification',
            'title' => 'Company Approved',
            'message' => 'Your company profile has been approved by the administrator.',
        ],
        'Rejected' => [
            'type' => 'verification',
            'title' => 'Company Verification Update',
            'message' => 'Your company verification request was not approved. Please review your company information.',
        ],
    ];

    if (isset($notificationMap[$verificationValue])) {
        $notificationData = $notificationMap[$verificationValue];
        $notification = generateNotification(
            $userId,
            $notificationData['type'],
            $notificationData['title'],
            $notificationData['message'],
            BASE_URL . 'notifications/index.php'
        );
        saveNotification($notification);
    }

    $_SESSION['success'] = 'Company verification status updated successfully.';
} else {
    $_SESSION['error'] = 'No changes were made.';
}

redirect('employers.php');
