<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');
$pageTitle = 'Upload CV';

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$conn = $GLOBALS['conn'];

$graduateStmt = $conn->prepare('SELECT cv FROM graduates WHERE user_id = ? LIMIT 1');
$graduateStmt->bind_param('i', $userId);
$graduateStmt->execute();
$graduateStmt->bind_result($cvFilename);
$graduateStmt->fetch();
$graduateStmt->close();

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
                        <h2 class="h5 fw-semibold mb-1">Upload CV</h2>
                        <p class="text-muted mb-0">Upload your CV in PDF or Word format so employers can review your experience.</p>
                    </div>
                    <a href="profile.php" class="btn btn-outline-custom">Back to Profile</a>
                </div>
            </div>

            <div class="card-ui p-4 bg-white">
                <?php if (!empty($cvFilename)): ?>
                    <div class="mb-4">
                        <p class="mb-1"><strong>Current CV:</strong> <?php echo htmlspecialchars($cvFilename); ?></p>
                        <a href="download_cv.php?file=<?php echo urlencode($cvFilename); ?>" class="btn btn-sm btn-outline-custom">Download Current CV</a>
                    </div>
                <?php endif; ?>

                <form action="upload_cv_process.php" method="POST" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                    <div class="mb-3">
                        <label for="cv_file" class="form-label">Choose CV file</label>
                        <input class="form-control" type="file" id="cv_file" name="cv_file" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                        <div class="form-text">Allowed file types: PDF, DOC, DOCX. Maximum size: 5MB.</div>
                    </div>
                    <button type="submit" class="btn btn-primary-custom">Upload CV</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
