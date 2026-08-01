<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please sign in with an administrator account.';
    redirect(BASE_URL . 'auth/login.php');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="gradconnect_jobs_report_' . date('Ymd_His') . '.csv"');

$output = fopen('php://output', 'w');
if ($output === false) {
    http_response_code(500);
    exit('Unable to open output stream.');
}

fputcsv($output, ['Job ID', 'Title', 'Company', 'Category', 'Location', 'Status', 'Posted At']);

$conn = $GLOBALS['conn'];
$stmt = $conn->prepare(
    'SELECT j.job_id, j.title, c.company_name, jc.category_name, j.location, j.status, j.created_at
     FROM jobs j
     LEFT JOIN companies c ON j.company_id = c.company_id
     LEFT JOIN job_categories jc ON j.category_id = jc.category_id
     ORDER BY j.created_at DESC'
);
$stmt->execute();
$stmt->bind_result($jobId, $title, $companyName, $categoryName, $location, $status, $createdAt);

while ($stmt->fetch()) {
    fputcsv($output, [
        $jobId,
        $title,
        $companyName,
        $categoryName,
        $location,
        $status,
        $createdAt,
    ]);
}

$stmt->close();
fclose($output);
exit;
