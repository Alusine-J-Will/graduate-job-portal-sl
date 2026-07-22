<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');
$pageTitle = 'Experience';

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$conn = $GLOBALS['conn'];

$graduateStmt = $conn->prepare('SELECT graduate_id FROM graduates WHERE user_id = ? LIMIT 1');
$graduateStmt->bind_param('i', $userId);
$graduateStmt->execute();
$graduateStmt->bind_result($graduateId);
$graduateStmt->fetch();
$graduateStmt->close();

$experienceItems = [];
$editItem = null;

if ($graduateId) {
    $listStmt = $conn->prepare('SELECT experience_id, organization, position, start_date, end_date, description FROM experience WHERE graduate_id = ? ORDER BY start_date DESC, experience_id DESC');
    $listStmt->bind_param('i', $graduateId);
    $listStmt->execute();
    $result = $listStmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $experienceItems[] = $row;
    }
    $listStmt->close();

    if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'edit') {
        $itemId = (int) $_GET['id'];
        $editStmt = $conn->prepare('SELECT experience_id, organization, position, start_date, end_date, description FROM experience WHERE experience_id = ? AND graduate_id = ? LIMIT 1');
        $editStmt->bind_param('ii', $itemId, $graduateId);
        $editStmt->execute();
        $editResult = $editStmt->get_result();
        $editItem = $editResult->fetch_assoc();
        $editStmt->close();
    }
}

$organization = sanitizeInput($editItem['organization'] ?? '');
$position = sanitizeInput($editItem['position'] ?? '');
$startDate = sanitizeInput($editItem['start_date'] ?? '');
$endDate = sanitizeInput($editItem['end_date'] ?? '');
$currentlyWorking = isset($editItem['end_date']) ? empty($editItem['end_date']) : false;
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
                        <h2 class="h5 fw-semibold mb-1">Work Experience</h2>
                        <p class="text-muted mb-0">Add, update, and delete professional roles you have held.</p>
                    </div>
                    <a href="profile.php" class="btn btn-outline-custom">Back to Profile</a>
                </div>
            </div>

            <div class="card-ui p-4 mb-4 bg-white">
                <form action="experience_process.php" method="POST" novalidate>
                    <input type="hidden" name="graduate_id" value="<?php echo $graduateId; ?>">
                    <input type="hidden" name="action" value="<?php echo $editItem ? 'update' : 'add'; ?>">
                    <?php if ($editItem): ?>
                        <input type="hidden" name="experience_id" value="<?php echo (int) $editItem['experience_id']; ?>">
                    <?php endif; ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="organization">Company</label>
                            <input type="text" class="form-control" id="organization" name="organization" value="<?php echo $organization; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="position">Job Title</label>
                            <input type="text" class="form-control" id="position" name="position" value="<?php echo $position; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="start_date">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="end_date">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>" <?php echo $currentlyWorking ? 'disabled' : ''; ?>>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="currently_working" name="currently_working" value="1" <?php echo $currentlyWorking ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="currently_working">I am currently working in this role</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo $description; ?></textarea>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary-custom"><?php echo $editItem ? 'Update Experience' : 'Add Experience'; ?></button>
                        <?php if ($editItem): ?>
                            <a href="experience.php" class="btn btn-outline-custom">Cancel Edit</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <?php if (empty($experienceItems)): ?>
                <div class="card-ui p-4 bg-white text-center">
                    <p class="mb-0 text-muted">No work experience added yet. Share your roles to strengthen your profile.</p>
                </div>
            <?php else: ?>
                <div class="timeline-list">
                    <?php foreach ($experienceItems as $item): ?>
                        <div class="card-ui p-4 bg-white mb-3">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <h3 class="h6 fw-semibold mb-1"><?php echo htmlspecialchars($item['position']); ?></h3>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($item['organization']); ?></p>
                                </div>
                                <div class="text-end">
                                    <span class="small text-muted"><?php echo htmlspecialchars($item['start_date'] ? date('M Y', strtotime($item['start_date'])) : ''); ?> - <?php echo htmlspecialchars($item['end_date'] ? date('M Y', strtotime($item['end_date'])) : 'Present'); ?></span>
                                </div>
                            </div>
                            <?php if (!empty($item['description'])): ?>
                                <p class="text-muted mb-3"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                            <?php endif; ?>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="experience.php?action=edit&id=<?php echo (int) $item['experience_id']; ?>" class="btn btn-sm btn-outline-custom">Edit</a>
                                <form action="experience_process.php" method="POST" class="m-0">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="experience_id" value="<?php echo (int) $item['experience_id']; ?>">
                                    <input type="hidden" name="graduate_id" value="<?php echo $graduateId; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this experience record?');">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkbox = document.querySelector('#currently_working');
        const endDateInput = document.querySelector('#end_date');

        if (!checkbox || !endDateInput) {
            return;
        }

        checkbox.addEventListener('change', function () {
            if (checkbox.checked) {
                endDateInput.value = '';
                endDateInput.disabled = true;
            } else {
                endDateInput.disabled = false;
            }
        });
    });
</script>
<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
