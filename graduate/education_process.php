<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    redirect('education.php');
}

$graduateId = isset($_POST['graduate_id']) ? (int) $_POST['graduate_id'] : 0;
$action = sanitizeInput($_POST['action'] ?? 'add');
$institution = sanitizeInput($_POST['institution'] ?? '');
$degree = sanitizeInput($_POST['degree'] ?? '');
$fieldOfStudy = sanitizeInput($_POST['field_of_study'] ?? '');
$graduationYear = sanitizeInput($_POST['graduation_year'] ?? '');
$grade = sanitizeInput($_POST['grade'] ?? '');

$errors = [];

if ($action !== 'delete') {
    if ($institution === '') {
        $errors[] = 'Institution is required.';
    }
    if ($degree === '') {
        $errors[] = 'Degree is required.';
    }
}

if (!empty($errors)) {
    $_SESSION['error'] = implode(' ', $errors);
    redirect('education.php');
}

$conn = $GLOBALS['conn'];

if ($action === 'add') {
    $stmt = $conn->prepare('INSERT INTO education (graduate_id, institution, degree, field_of_study, graduation_year, grade) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('isssss', $graduateId, $institution, $degree, $fieldOfStudy, $graduationYear, $grade);
    $success = $stmt->execute();
    $stmt->close();
    if ($success) {
        refreshGraduateProfileCompletion($conn, $_SESSION['user_id']);
    }
    $_SESSION['success'] = $success ? 'Education record added.' : 'Unable to add education record.';
} elseif ($action === 'update') {
    $educationId = isset($_POST['education_id']) ? (int) $_POST['education_id'] : 0;
    $stmt = $conn->prepare('UPDATE education SET institution = ?, degree = ?, field_of_study = ?, graduation_year = ?, grade = ? WHERE education_id = ? AND graduate_id = ?');
    $stmt->bind_param('sssssii', $institution, $degree, $fieldOfStudy, $graduationYear, $grade, $educationId, $graduateId);
    $success = $stmt->execute();
    $stmt->close();
    if ($success) {
        refreshGraduateProfileCompletion($conn, $_SESSION['user_id']);
    }
    $_SESSION['success'] = $success ? 'Education record updated.' : 'Unable to update education record.';
} elseif ($action === 'delete') {
    $educationId = isset($_POST['education_id']) ? (int) $_POST['education_id'] : 0;
    $stmt = $conn->prepare('DELETE FROM education WHERE education_id = ? AND graduate_id = ?');
    $stmt->bind_param('ii', $educationId, $graduateId);
    $success = $stmt->execute();
    $stmt->close();
    if ($success) {
        refreshGraduateProfileCompletion($conn, $_SESSION['user_id']);
    }
    $_SESSION['success'] = $success ? 'Education record deleted.' : 'Unable to delete education record.';
} else {
    $_SESSION['error'] = 'Invalid action.';
    redirect('education.php');
}

redirect('education.php');
