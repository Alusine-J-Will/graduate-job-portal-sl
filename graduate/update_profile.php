<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    redirect('edit_profile.php');
}

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$fullName = sanitizeInput($_POST['full_name'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');
$location = sanitizeInput($_POST['location'] ?? '');
$bio = sanitizeInput($_POST['bio'] ?? '');

$errors = [];

if ($fullName === '') {
    $errors[] = 'Full Name is required.';
}

if ($phone === '') {
    $errors[] = 'Phone Number is required.';
}

if ($location === '') {
    $errors[] = 'Location is required.';
}

if ($bio === '') {
    $errors[] = 'Bio is required.';
}

if (!empty($errors)) {
    $_SESSION['error'] = implode(' ', $errors);
    redirect('edit_profile.php');
}

$conn = $GLOBALS['conn'];
$conn->begin_transaction();

$userUpdate = $conn->prepare('UPDATE users SET full_name = ?, phone = ?, updated_at = NOW() WHERE user_id = ?');
$userUpdate->bind_param('ssi', $fullName, $phone, $userId);
$userUpdated = $userUpdate->execute();
$userUpdate->close();

$graduateUpdate = $conn->prepare('UPDATE graduates SET location = ?, bio = ?, updated_at = NOW() WHERE user_id = ?');
$graduateUpdate->bind_param('ssi', $location, $bio, $userId);
$graduateUpdated = $graduateUpdate->execute();
$graduateUpdate->close();

$profileCompletion = 0;
$educationCount = 0;
$experienceCount = 0;
$skillCount = 0;
$cvExists = false;
$photoExists = false;

if ($userUpdated && $graduateUpdated) {
    $countStmt = $conn->prepare('SELECT COUNT(*) FROM education WHERE graduate_id = (SELECT graduate_id FROM graduates WHERE user_id = ? LIMIT 1)');
    $countStmt->bind_param('i', $userId);
    $countStmt->execute();
    $countStmt->bind_result($educationCount);
    $countStmt->fetch();
    $countStmt->close();

    $countStmt = $conn->prepare('SELECT COUNT(*) FROM experience WHERE graduate_id = (SELECT graduate_id FROM graduates WHERE user_id = ? LIMIT 1)');
    $countStmt->bind_param('i', $userId);
    $countStmt->execute();
    $countStmt->bind_result($experienceCount);
    $countStmt->fetch();
    $countStmt->close();

    $countStmt = $conn->prepare('SELECT COUNT(*) FROM graduate_skills WHERE graduate_id = (SELECT graduate_id FROM graduates WHERE user_id = ? LIMIT 1)');
    $countStmt->bind_param('i', $userId);
    $countStmt->execute();
    $countStmt->bind_result($skillCount);
    $countStmt->fetch();
    $countStmt->close();

    $cvStmt = $conn->prepare('SELECT cv, profile_picture FROM graduates WHERE user_id = ? LIMIT 1');
    $cvStmt->bind_param('i', $userId);
    $cvStmt->execute();
    $cvStmt->bind_result($cv, $profilePicture);
    $cvStmt->fetch();
    $cvStmt->close();

    $cvExists = !empty($cv);
    $photoExists = !empty($profilePicture);

    $completionStatus = [
        'personal' => true,
        'photo' => $photoExists,
        'location' => $location !== '',
        'bio' => $bio !== '',
        'education' => $educationCount > 0,
        'experience' => $experienceCount > 0,
        'skills' => $skillCount > 0,
        'cv' => $cvExists,
    ];

    $profileCompletion = calculateProfileCompletion($completionStatus);

    if (columnExists($conn, 'graduates', 'profile_completion')) {
        $completionStmt = $conn->prepare('UPDATE graduates SET profile_completion = ? WHERE user_id = ?');
        $completionStmt->bind_param('ii', $profileCompletion, $userId);
        $completionStmt->execute();
        $completionStmt->close();
    }
}

if ($userUpdated && $graduateUpdated) {
    refreshGraduateProfileCompletion($conn, $userId);
    $conn->commit();
    $_SESSION['success'] = 'Profile updated successfully.';
    redirect('profile.php');
}

$conn->rollback();
$_SESSION['error'] = 'Unable to update your profile. Please try again.';
redirect('edit_profile.php');
