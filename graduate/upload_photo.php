<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');
$pageTitle = 'Upload Profile Photo';

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$conn = $GLOBALS['conn'];

$graduateStmt = $conn->prepare('SELECT profile_picture FROM graduates WHERE user_id = ? LIMIT 1');
$graduateStmt->bind_param('i', $userId);
$graduateStmt->execute();
$graduateStmt->bind_result($profilePicture);
$graduateStmt->fetch();
$graduateStmt->close();

$avatarSrc = !empty($profilePicture) ? BASE_URL . 'uploads/profile_photos/' . htmlspecialchars($profilePicture) : null;

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
                        <h2 class="h5 fw-semibold mb-1">Upload Profile Photo</h2>
                        <p class="text-muted mb-0">Add a professional photo so employers can recognize your profile.</p>
                    </div>
                    <a href="profile.php" class="btn btn-outline-custom">Back to Profile</a>
                </div>
            </div>

            <div class="card-ui p-4 bg-white">
                <?php if ($avatarSrc): ?>
                    <div class="mb-4 text-center">
                        <img src="<?php echo $avatarSrc; ?>" alt="Current profile photo" class="rounded-circle shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                    </div>
                <?php endif; ?>

                <form action="upload_photo_process.php" method="POST" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                    <div class="mb-3">
                        <label for="profile_photo" class="form-label">Choose a profile photo</label>
                        <input class="form-control" type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png" required>
                        <div class="form-text">Allowed file types: JPG, JPEG, PNG. Maximum size: 5MB.</div>
                    </div>
                    <button type="submit" class="btn btn-primary-custom">Upload Photo</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
