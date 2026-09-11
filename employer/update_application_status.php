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
$status = normalizeApplicationStatus($_POST['status'] ?? '');
$allowedStatuses = ['pending', 'under_review', 'shortlisted', 'interview_scheduled', 'accepted', 'rejected'];
$interviewDate = trim($_POST['interview_date'] ?? '');
$interviewTime = trim($_POST['interview_time'] ?? '');

if ($applicationId <= 0 || !in_array($status, $allowedStatuses, true)) {
    $_SESSION['error'] = 'Invalid application status update request.';
    redirect('applicants.php');
}

if ($status === 'interview_scheduled') {
    $dateValue = DateTime::createFromFormat('!Y-m-d', $interviewDate);
    $dateErrors = DateTime::getLastErrors();
    $dateIsValid = $dateValue !== false && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0)) && $dateValue->format('Y-m-d') === $interviewDate;
    $timeValue = DateTime::createFromFormat('!H:i', $interviewTime);
    $timeErrors = DateTime::getLastErrors();
    $timeIsValid = $timeValue !== false && ($timeErrors === false || ($timeErrors['warning_count'] === 0 && $timeErrors['error_count'] === 0)) && $timeValue->format('H:i') === $interviewTime;

    if (!$dateIsValid || !$timeIsValid) {
        $_SESSION['error'] = 'A valid interview date and time are required when scheduling an interview.';
        redirect('application_details.php?application_id=' . $applicationId);
    }
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
    'SELECT a.application_id, a.graduate_id, a.status, a.job_id, a.interview_date, a.interview_time,
            g.user_id AS graduate_user_id, j.title AS job_title, c.company_name
     FROM applications a
     INNER JOIN jobs j ON a.job_id = j.job_id
     INNER JOIN graduates g ON a.graduate_id = g.graduate_id
     INNER JOIN companies c ON j.company_id = c.company_id
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

$dateValue = null;
$timeValue = null;
if ($status === 'interview_scheduled') {
    $dateValue = DateTime::createFromFormat('!Y-m-d', $interviewDate);
    $timeValue = DateTime::createFromFormat('!H:i', $interviewTime);
    $updateStmt = $conn->prepare(
        'UPDATE applications
         SET status = ?, interview_date = ?, interview_time = ?, updated_at = NOW()
         WHERE application_id = ?'
    );
    $updateStmt->bind_param('sssi', $status, $interviewDate, $interviewTime, $applicationId);
} else {
    $updateStmt = $conn->prepare('UPDATE applications SET status = ?, updated_at = NOW() WHERE application_id = ?');
    $updateStmt->bind_param('si', $status, $applicationId);
}
$updateStmt->execute();
$updateStmt->close();

$statusNotificationMap = [
    'under_review' => [
        'title' => 'Application Under Review',
        'message' => sprintf('Your application for %s is now under review.', $application['job_title']),
        'type' => 'application_status',
        'email_event' => 'application_under_review',
    ],
    'shortlisted' => [
        'title' => 'Application Shortlisted',
        'message' => sprintf('Congratulations! Your application for %s has been shortlisted.', $application['job_title']),
        'type' => 'application_status',
        'email_event' => 'application_shortlisted',
    ],
    'interview_scheduled' => [
        'title' => sprintf('Interview Scheduled — %s', $application['job_title']),
        'message' => sprintf(
            'Your interview for the %s position at %s has been scheduled for %s at %s.',
            $application['job_title'],
            $application['company_name'],
            $dateValue ? $dateValue->format('l, j F Y') : '',
            $timeValue ? $timeValue->format('g:i A') : ''
        ),
        'type' => 'application_status',
        'email_event' => 'application_interview_scheduled',
    ],
    'accepted' => [
        'title' => 'Application Accepted',
        'message' => sprintf('Congratulations! Your application for %s has been accepted.', $application['job_title']),
        'type' => 'application_status',
        'email_event' => 'application_accepted',
    ],
    'rejected' => [
        'title' => 'Application Update',
        'message' => sprintf('Your application for %s was not selected at this time.', $application['job_title']),
        'type' => 'application_status',
        'email_event' => 'application_rejected',
    ],
];

if ($status !== normalizeApplicationStatus($application['status'] ?? 'pending') && isset($statusNotificationMap[$status])) {
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
            $emailEvent = $statusNotificationMap[$status]['email_event'] ?? null;
            if ($emailEvent !== null) {
                sendApplicationEmail(
                    $graduateAccount['email'],
                    $graduateAccount['full_name'] ?? 'Graduate',
                    $emailEvent,
                    [
                        'job_title' => $application['job_title'],
                        'company_name' => $application['company_name'],
                        'interview_date' => $dateValue ? $dateValue->format('l, j F Y') : '',
                        'interview_time' => $timeValue ? $timeValue->format('g:i A') : '',
                        'application_id' => $applicationId,
                    ]
                );
            }
        }
    }
}

$_SESSION['success'] = 'Application status updated successfully.';
redirect('application_details.php?application_id=' . $applicationId);
