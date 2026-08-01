<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please sign in with an administrator account.';
    redirect(BASE_URL . 'auth/login.php');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="gradconnect_applications_report_' . date('Ymd_His') . '.csv"');

$output = fopen('php://output', 'w');
if ($output === false) {
    http_response_code(500);
    exit('Unable to open output stream.');
}

fputcsv($output, ['Application ID', 'Graduate Name', 'Job Title', 'Company', 'Status', 'Applied At']);

$conn = $GLOBALS['conn'];
$stmt = $conn->prepare(
    'SELECT a.application_id, u.full_name AS graduate_name, j.title AS job_title, c.company_name, a.status, a.application_date
     FROM applications a
     INNER JOIN graduates g ON a.graduate_id = g.graduate_id
     INNER JOIN users u ON g.user_id = u.user_id
     INNER JOIN jobs j ON a.job_id = j.job_id
     LEFT JOIN companies c ON j.company_id = c.company_id
     ORDER BY a.application_date DESC'
);
$stmt->execute();
$stmt->bind_result($applicationId, $graduateName, $jobTitle, $companyName, $status, $applicationDate);

while ($stmt->fetch()) {
    fputcsv($output, [
        $applicationId,
        $graduateName,
        $jobTitle,
        $companyName,
        $status,
        $applicationDate,
    ]);
}

$stmt->close();
fclose($output);
exit;
