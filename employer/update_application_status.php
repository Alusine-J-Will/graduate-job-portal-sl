<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/email_helper.php';
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
    'SELECT a.application_id, a.status, a.job_id, g.user_id AS graduate_user_id, j.title AS job_title
     FROM applications a
     INNER JOIN jobs j ON a.job_id = j.job_id
     INNER JOIN graduates g ON a.graduate_id = g.graduate_id
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

$statusNotificationMap = [
    'Reviewed' => [
        'title' => 'Application Reviewed',
        'message' => sprintf('Your application for %s has been reviewed.', $application['job_title']),
        'type' => 'application_status',
    ],
    'Shortlisted' => [
        'title' => 'Application Shortlisted',
        'message' => sprintf('Congratulations! Your application for %s has been shortlisted.', $application['job_title']),
        'type' => 'application_status',
    ],
    'Accepted' => [
        'title' => 'Application Accepted',
        'message' => sprintf('Congratulations! Your application for %s has been accepted.', $application['job_title']),
        'type' => 'application_status',
    ],
    'Rejected' => [
        'title' => 'Application Update',
        'message' => sprintf('Your application for %s was not selected at this time.', $application['job_title']),
        'type' => 'application_status',
    ],
];

if ($status !== $application['status'] && isset($statusNotificationMap[$status])) {
    $notificationData = $statusNotificationMap[$status];
    $notification = generateNotification(
        (int) $application['graduate_user_id'],
        $notificationData['type'],
        $notificationData['title'],
        $notificationData['message'],
        BASE_URL . 'graduate/application_details.php?application_id=' . $applicationId
    );

    if (!saveNotification($notification)) {
        error_log('Failed to create graduate application status notification for application_id=' . $applicationId);
    }

    $graduateEmailStmt = $conn->prepare('SELECT email, full_name FROM users u INNER JOIN graduates g ON g.user_id = u.user_id WHERE g.graduate_id = ? LIMIT 1');
    if ($graduateEmailStmt) {
        $graduateId = (int) ($application['graduate_id'] ?? 0);
        $graduateEmailStmt->bind_param('i', $graduateId);
        $graduateEmailStmt->execute();
        $graduateEmailResult = $graduateEmailStmt->get_result();
        $graduateAccount = $graduateEmailResult->fetch_assoc();
        $graduateEmailStmt->close();

        if (!empty($graduateAccount['email'])) {
            $eventType = match ($status) {
                'Reviewed' => 'application_reviewed',
                'Shortlisted' => 'application_shortlisted',
                'Accepted' => 'application_accepted',
                'Rejected' => 'application_rejected',
                default => null,
            };

            if ($eventType !== null) {
                sendApplicationEmail(
                    $graduateAccount['email'],
                    $graduateAccount['full_name'] ?? 'Graduate',
                    $eventType,
                    ['job_title' => $application['job_title'], 'application_id' => $applicationId]
                );
            }
        }
    }
}

$_SESSION['success'] = 'Application status updated successfully.';
redirect('application_details.php?application_id=' . $applicationId);
