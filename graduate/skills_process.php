<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    redirect('skills.php');
}

$graduateId = isset($_POST['graduate_id']) ? (int) $_POST['graduate_id'] : 0;
$action = sanitizeInput($_POST['action'] ?? 'add');

$conn = $GLOBALS['conn'];

if ($action === 'add') {
    $skillName = sanitizeInput($_POST['skill_name'] ?? '');

    if ($skillName === '') {
        $_SESSION['error'] = 'Please enter a skill name.';
        redirect('skills.php');
    }

    $conn->begin_transaction();

    $skillStmt = $conn->prepare('SELECT skill_id FROM skills WHERE skill_name = ? LIMIT 1');
    $skillStmt->bind_param('s', $skillName);
    $skillStmt->execute();
    $skillStmt->bind_result($skillId);
    $skillStmt->fetch();
    $skillStmt->close();

    if (!$skillId) {
        $insertStmt = $conn->prepare('INSERT INTO skills (skill_name) VALUES (?)');
        $insertStmt->bind_param('s', $skillName);
        $insertStmt->execute();
        $skillId = $insertStmt->insert_id;
        $insertStmt->close();
    }

    $linkStmt = $conn->prepare('INSERT IGNORE INTO graduate_skills (graduate_id, skill_id) VALUES (?, ?)');
    $linkStmt->bind_param('ii', $graduateId, $skillId);
    $linkStmt->execute();
    $linkStmt->close();

    $conn->commit();
    refreshGraduateProfileCompletion($conn, $_SESSION['user_id']);
    $_SESSION['success'] = 'Skill added successfully.';
} elseif ($action === 'delete') {
    $skillId = isset($_POST['skill_id']) ? (int) $_POST['skill_id'] : 0;

    if ($skillId <= 0) {
        $_SESSION['error'] = 'Invalid skill selected for removal.';
        redirect('skills.php');
    }

    $stmt = $conn->prepare('DELETE FROM graduate_skills WHERE graduate_id = ? AND skill_id = ?');
    $stmt->bind_param('ii', $graduateId, $skillId);
    $success = $stmt->execute();
    $stmt->close();
    if ($success) {
        refreshGraduateProfileCompletion($conn, $_SESSION['user_id']);
    }

    $_SESSION['success'] = 'Skill removed successfully.';
} else {
    $_SESSION['error'] = 'Invalid action.';
}

redirect('skills.php');
