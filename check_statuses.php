<?php
require 'config/config.php';
$conn = $GLOBALS['conn'];
$result = $conn->query('SELECT DISTINCT status FROM applications');
echo "Actual application statuses in DB:\n";
while ($row = $result->fetch_assoc()) {
    echo "  - " . $row['status'] . "\n";
}

echo "\nAllowed statuses in update_application_status.php:\n";
$allowed = ['pending', 'under_review', 'shortlisted', 'interview_scheduled', 'accepted', 'rejected'];
foreach ($allowed as $status) {
    echo "  - " . $status . "\n";
}
?>
