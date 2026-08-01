<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');
$pageTitle = 'Skills';

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$conn = $GLOBALS['conn'];

$graduateStmt = $conn->prepare('SELECT graduate_id FROM graduates WHERE user_id = ? LIMIT 1');
$graduateStmt->bind_param('i', $userId);
$graduateStmt->execute();
$graduateStmt->bind_result($graduateId);
$graduateStmt->fetch();
$graduateStmt->close();

$skills = [];

if ($graduateId) {
    $listStmt = $conn->prepare('SELECT s.skill_id, s.skill_name FROM skills s JOIN graduate_skills gs ON s.skill_id = gs.skill_id WHERE gs.graduate_id = ? ORDER BY s.skill_name');
    $listStmt->bind_param('i', $graduateId);
    $listStmt->execute();
    $result = $listStmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $skills[] = $row;
    }
    $listStmt->close();
}

$skillName = sanitizeInput($_POST['skill_name'] ?? '');

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/dashboard_topbar.php';
?>
<div class="container-fluid py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>
        <div class="col-lg-9">
            <?php displayFlashMessages(); ?>

            <div class="card-ui p-4 mb-4 bg-white">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <div>
                        <h2 class="h5 fw-semibold mb-1">Skills</h2>
                        <p class="text-muted mb-0">Add and remove your technical and soft skills.</p>
                    </div>
                    <a href="profile.php" class="btn btn-outline-custom">Back to Profile</a>
                </div>
            </div>

            <div class="card-ui p-4 mb-4 bg-white">
                <form action="skills_process.php" method="POST" novalidate>
                    <input type="hidden" name="graduate_id" value="<?php echo $graduateId; ?>">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label" for="skill_name">Add Skill</label>
                            <input type="text" class="form-control" id="skill_name" name="skill_name" placeholder="e.g. PHP, JavaScript, Communication" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary-custom w-100">Add Skill</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-ui p-4 bg-white">
                <?php if (empty($skills)): ?>
                    <p class="mb-0 text-muted">No skills added yet. Add skills to strengthen your profile.</p>
                <?php else: ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($skills as $skill): ?>
                            <div class="badge rounded-pill bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center gap-2 py-2 px-3">
                                <?php echo htmlspecialchars($skill['skill_name']); ?>
                                <form action="skills_process.php" method="POST" class="m-0">
                                    <input type="hidden" name="graduate_id" value="<?php echo $graduateId; ?>">
                                    <input type="hidden" name="skill_id" value="<?php echo (int) $skill['skill_id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-close btn-close-white btn-sm" aria-label="Delete"></button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
