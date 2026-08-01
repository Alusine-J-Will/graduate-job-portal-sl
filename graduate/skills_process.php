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
    $rawSkills = $_POST['skill_name'] ?? '';
    $skillItems = preg_split('/[\n,]+/', (string) $rawSkills);
    $skillItems = array_map(static fn($skill) => sanitizeInput(trim($skill)), $skillItems);
    $skillItems = array_filter(array_unique($skillItems), static fn($skill) => $skill !== '');

    if (empty($skillItems)) {
        $_SESSION['error'] = 'Please enter at least one skill.';
        redirect('skills.php');
    }

    $conn->begin_transaction();
    $skillStmt = $conn->prepare('SELECT skill_id FROM skills WHERE skill_name = ? LIMIT 1');
    $insertSkillStmt = $conn->prepare('INSERT INTO skills (skill_name) VALUES (?)');
    $linkStmt = $conn->prepare('INSERT IGNORE INTO graduate_skills (graduate_id, skill_id) VALUES (?, ?)');

    $skillsAdded = 0;
    foreach ($skillItems as $skillName) {
        $skillId = null;

        $skillStmt->bind_param('s', $skillName);
        $skillStmt->execute();
        $skillStmt->bind_result($skillId);
        $skillStmt->fetch();
        $skillStmt->free_result();

        if (!$skillId) {
            $insertSkillStmt->bind_param('s', $skillName);
            $insertSkillStmt->execute();
            $skillId = $insertSkillStmt->insert_id;
        }

        $linkStmt->bind_param('ii', $graduateId, $skillId);
        $linkStmt->execute();
        if ($linkStmt->affected_rows > 0) {
            $skillsAdded++;
        }
    }

    $skillStmt->close();
    $insertSkillStmt->close();
    $linkStmt->close();
    $conn->commit();
    refreshGraduateProfileCompletion($conn, $_SESSION['user_id']);
    $_SESSION['success'] = $skillsAdded . ' skill' . ($skillsAdded === 1 ? '' : 's') . ' added successfully.';
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
