<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');
$pageTitle = 'Education';

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$conn = $GLOBALS['conn'];

$graduateStmt = $conn->prepare('SELECT graduate_id FROM graduates WHERE user_id = ? LIMIT 1');
$graduateStmt->bind_param('i', $userId);
$graduateStmt->execute();
$graduateStmt->bind_result($graduateId);
$graduateStmt->fetch();
$graduateStmt->close();

$educationItems = [];
$editItem = null;

if ($graduateId) {
    $listStmt = $conn->prepare('SELECT education_id, institution, degree, field_of_study, graduation_year, grade FROM education WHERE graduate_id = ? ORDER BY graduation_year DESC, education_id DESC');
    $listStmt->bind_param('i', $graduateId);
    $listStmt->execute();
    $result = $listStmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $educationItems[] = $row;
    }
    $listStmt->close();

    if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'edit') {
        $itemId = (int) $_GET['id'];
        $editStmt = $conn->prepare('SELECT education_id, institution, degree, field_of_study, graduation_year, grade FROM education WHERE education_id = ? AND graduate_id = ? LIMIT 1');
        $editStmt->bind_param('ii', $itemId, $graduateId);
        $editStmt->execute();
        $editResult = $editStmt->get_result();
        $editItem = $editResult->fetch_assoc();
        $editStmt->close();
    }
}

$institution = sanitizeInput($editItem['institution'] ?? '');
$degree = sanitizeInput($editItem['degree'] ?? '');
$fieldOfStudy = sanitizeInput($editItem['field_of_study'] ?? '');
$graduationYear = sanitizeInput($editItem['graduation_year'] ?? '');
$grade = sanitizeInput($editItem['grade'] ?? '');
$description = sanitizeInput($editItem['description'] ?? '');

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
                        <h2 class="h5 fw-semibold mb-1">Education History</h2>
                        <p class="text-muted mb-0">Add, edit, and remove your education records.</p>
                    </div>
                    <a href="profile.php" class="btn btn-outline-custom">Back to Profile</a>
                </div>
            </div>

            <div class="card-ui p-4 mb-4 bg-white">
                <form action="education_process.php" method="POST" novalidate>
                    <input type="hidden" name="graduate_id" value="<?php echo $graduateId; ?>">
                    <input type="hidden" name="action" value="<?php echo $editItem ? 'update' : 'add'; ?>">
                    <?php if ($editItem): ?>
                        <input type="hidden" name="education_id" value="<?php echo (int) $editItem['education_id']; ?>">
                    <?php endif; ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="institution">Institution</label>
                            <input type="text" class="form-control" id="institution" name="institution" value="<?php echo $institution; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="degree">Degree</label>
                            <input type="text" class="form-control" id="degree" name="degree" value="<?php echo $degree; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="field_of_study">Field of Study</label>
                            <input type="text" class="form-control" id="field_of_study" name="field_of_study" value="<?php echo $fieldOfStudy; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="graduation_year">Graduation Year</label>
                            <input type="number" min="1900" max="2099" step="1" class="form-control" id="graduation_year" name="graduation_year" value="<?php echo $graduationYear; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="grade">Grade</label>
                            <input type="text" class="form-control" id="grade" name="grade" value="<?php echo $grade; ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="description">Description (Projects, Awards, Activities)</label>
                            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Projects during studies: E.g., E-learning Portal. President of Tech Club."><?php echo $description; ?></textarea>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary-custom"><?php echo $editItem ? 'Update Record' : 'Add Education'; ?></button>
                        <?php if ($editItem): ?>
                            <a href="education.php" class="btn btn-outline-custom">Cancel Edit</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <?php if (empty($educationItems)): ?>
                <div class="card-ui p-4 bg-white text-center">
                    <p class="mb-0 text-muted">No education records found. Add your first qualification to build your profile.</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($educationItems as $item): ?>
                        <div class="col-md-6">
                            <div class="card-ui p-4 bg-white h-100">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h3 class="h6 fw-semibold mb-1"><?php echo htmlspecialchars($item['degree']); ?></h3>
                                        <p class="text-muted mb-1"><?php echo htmlspecialchars($item['institution']); ?></p>
                                        <p class="small text-muted mb-0"><?php echo htmlspecialchars($item['field_of_study'] ?? ''); ?></p>
                                    </div>
                                    <div class="text-end">
                                        <?php if (!empty($item['graduation_year'])): ?>
                                            <span class="badge bg-primary bg-opacity-10 text-primary"><?php echo htmlspecialchars($item['graduation_year']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (!empty($item['grade'])): ?>
                                    <p class="mb-2"><strong>Grade:</strong> <?php echo htmlspecialchars($item['grade']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($item['description'])): ?>
                                    <p class="text-muted mb-3"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                                <?php endif; ?>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="education.php?action=edit&id=<?php echo (int) $item['education_id']; ?>" class="btn btn-sm btn-outline-custom">Edit</a>
                                    <form action="education_process.php" method="POST" class="m-0">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="education_id" value="<?php echo (int) $item['education_id']; ?>">
                                        <input type="hidden" name="graduate_id" value="<?php echo $graduateId; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this education record?');">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
