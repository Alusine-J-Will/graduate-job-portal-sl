<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please sign in with an administrator account.';
    redirect(BASE_URL . 'auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('applications.php');
}

$applicationId = isset($_POST['application_id']) ? (int) $_POST['application_id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$status = normalizeApplicationStatus($status);

if ($applicationId <= 0 || !in_array($status, ['pending', 'under_review', 'shortlisted', 'interview_scheduled', 'accepted', 'rejected'], true)) {
    $_SESSION['error'] = 'Invalid application status request.';
    redirect('applications.php');
}

$conn = $GLOBALS['conn'];

// Fetch current application details for notification and email
$applicationStmt = $conn->prepare(
    'SELECT a.application_id, a.graduate_id, a.status, a.job_id, g.user_id AS graduate_user_id, j.title AS job_title, c.company_id
     FROM applications a
     INNER JOIN jobs j ON a.job_id = j.job_id
     INNER JOIN graduates g ON a.graduate_id = g.graduate_id
     INNER JOIN companies c ON j.company_id = c.company_id
     WHERE a.application_id = ? LIMIT 1'
);
$applicationStmt->bind_param('i', $applicationId);
$applicationStmt->execute();
$applicationResult = $applicationStmt->get_result();
$application = $applicationResult->fetch_assoc();
$applicationStmt->close();

if (!$application) {
    $_SESSION['error'] = 'Application not found.';
    redirect('applications.php');
}

// Update application status
$updateStmt = $conn->prepare('UPDATE applications SET status = ?, updated_at = NOW() WHERE application_id = ?');
$updateStmt->bind_param('si', $status, $applicationId);
$updateStmt->execute();
$updateStmt->close();

if ($conn->affected_rows > 0) {
    $_SESSION['success'] = 'Application status updated successfully.';
    
    // Send notification and email if status changed
    if ($status !== normalizeApplicationStatus($application['status'] ?? 'pending')) {
        // Map admin statuses to email event types
        $statusEmailMap = [
            'under_review' => 'application_under_review',
            'shortlisted' => 'application_shortlisted',
            'accepted' => 'application_accepted',
            'rejected' => 'application_rejected',
        ];
        
        if (isset($statusEmailMap[$status])) {
            // Fetch graduate email
            $graduateEmailStmt = $conn->prepare('SELECT email, full_name FROM users WHERE user_id = ? LIMIT 1');
            $graduateEmailStmt->bind_param('i', $application['graduate_user_id']);
            $graduateEmailStmt->execute();
            $graduateEmailResult = $graduateEmailStmt->get_result();
            $graduateAccount = $graduateEmailResult->fetch_assoc();
            $graduateEmailStmt->close();
            
            if (!empty($graduateAccount['email'])) {
                // Fetch company name
                $companyStmt = $conn->prepare(
                    'SELECT company_name FROM companies WHERE company_id = ? LIMIT 1'
                );
                $companyStmt->bind_param('i', $application['company_id']);
                $companyStmt->execute();
                $companyResult = $companyStmt->get_result();
                $companyData = $companyResult->fetch_assoc();
                $companyStmt->close();
                
                // Send email notification
                $emailEvent = $statusEmailMap[$status];
                sendApplicationEmail(
                    $graduateAccount['email'],
                    $graduateAccount['full_name'] ?? 'Graduate',
                    $emailEvent,
                    [
                        'job_title' => $application['job_title'],
                        'company_name' => $companyData['company_name'] ?? '',
                        'application_id' => $applicationId,
                    ]
                );
            }
        }
    }
} else {
    $_SESSION['error'] = 'No changes were made.';
}

redirect('applications.php');
