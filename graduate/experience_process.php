<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    redirect('experience.php');
}

$graduateId = isset($_POST['graduate_id']) ? (int) $_POST['graduate_id'] : 0;
$action = sanitizeInput($_POST['action'] ?? 'add');
$organization = sanitizeInput($_POST['organization'] ?? '');
$position = sanitizeInput($_POST['position'] ?? '');
$startDate = sanitizeInput($_POST['start_date'] ?? '');
$currentlyWorking = isset($_POST['currently_working']) ? true : false;
$endDate = $currentlyWorking ? null : sanitizeInput($_POST['end_date'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');

$errors = [];

if ($action !== 'delete') {
    if ($organization === '') {
        $errors[] = 'Company is required.';
    }
    if ($position === '') {
        $errors[] = 'Job Title is required.';
    }
}

if (!empty($errors)) {
    $_SESSION['error'] = implode(' ', $errors);
    redirect('experience.php');
}

$conn = $GLOBALS['conn'];

if ($action === 'add') {
    $stmt = $conn->prepare('INSERT INTO experience (graduate_id, organization, position, start_date, end_date, description) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('isssss', $graduateId, $organization, $position, $startDate, $endDate, $description);
    $success = $stmt->execute();
    $stmt->close();
    if ($success) {
        refreshGraduateProfileCompletion($conn, $_SESSION['user_id']);
    }
    $_SESSION['success'] = $success ? 'Experience record added.' : 'Unable to add experience record.';
} elseif ($action === 'update') {
    $experienceId = isset($_POST['experience_id']) ? (int) $_POST['experience_id'] : 0;
    $stmt = $conn->prepare('UPDATE experience SET organization = ?, position = ?, start_date = ?, end_date = ?, description = ? WHERE experience_id = ? AND graduate_id = ?');
    $stmt->bind_param('sssssii', $organization, $position, $startDate, $endDate, $description, $experienceId, $graduateId);
    $success = $stmt->execute();
    $stmt->close();
    if ($success) {
        refreshGraduateProfileCompletion($conn, $_SESSION['user_id']);
    }
    $_SESSION['success'] = $success ? 'Experience record updated.' : 'Unable to update experience record.';
} elseif ($action === 'delete') {
    $experienceId = isset($_POST['experience_id']) ? (int) $_POST['experience_id'] : 0;
    $stmt = $conn->prepare('DELETE FROM experience WHERE experience_id = ? AND graduate_id = ?');
    $stmt->bind_param('ii', $experienceId, $graduateId);
    $success = $stmt->execute();
    $stmt->close();
    if ($success) {
        refreshGraduateProfileCompletion($conn, $_SESSION['user_id']);
    }
    $_SESSION['success'] = $success ? 'Experience record deleted.' : 'Unable to delete experience record.';
} else {
    $_SESSION['error'] = 'Invalid action.';
    redirect('experience.php');
}

redirect('experience.php');
