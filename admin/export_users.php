<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please sign in with an administrator account.';
    redirect(BASE_URL . 'auth/login.php');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="gradconnect_users_report_' . date('Ymd_His') . '.csv"');

$output = fopen('php://output', 'w');
if ($output === false) {
    http_response_code(500);
    exit('Unable to open output stream.');
}

fputcsv($output, ['User ID', 'Full Name', 'Email', 'Phone', 'Role', 'Status', 'Registered At']);

$conn = $GLOBALS['conn'];
$stmt = $conn->prepare('SELECT user_id, full_name, email, phone, role, status, created_at FROM users ORDER BY created_at DESC');
$stmt->execute();
$stmt->bind_result($userId, $fullName, $email, $phone, $role, $status, $createdAt);

while ($stmt->fetch()) {
    fputcsv($output, [
        $userId,
        $fullName,
        $email,
        $phone,
        ucfirst($role),
        ucfirst($status),
        $createdAt,
    ]);
}

$stmt->close();
fclose($output);
exit;
