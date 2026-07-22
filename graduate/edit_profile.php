<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');
$pageTitle = 'Edit Profile';

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$conn = $GLOBALS['conn'];

$userStmt = $conn->prepare('SELECT full_name, email, phone FROM users WHERE user_id = ? LIMIT 1');
$userStmt->bind_param('i', $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();
$userStmt->close();

$graduateStmt = $conn->prepare('SELECT graduate_id, location, bio FROM graduates WHERE user_id = ? LIMIT 1');
$graduateStmt->bind_param('i', $userId);
$graduateStmt->execute();
$graduateResult = $graduateStmt->get_result();
$graduate = $graduateResult->fetch_assoc();
$graduateStmt->close();

$fullName = sanitizeInput($user['full_name'] ?? '');
$email = sanitizeInput($user['email'] ?? '');
$phone = sanitizeInput($user['phone'] ?? '');
$location = sanitizeInput($graduate['location'] ?? '');
$bio = sanitizeInput($graduate['bio'] ?? '');

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
                        <h2 class="h5 fw-semibold mb-1">Edit Personal Information</h2>
                        <p class="text-muted mb-0">Update your name, phone, location, and bio so employers can find you.</p>
                    </div>
                    <a href="profile.php" class="btn btn-outline-custom">Back to Profile</a>
                </div>
            </div>

            <div class="card-ui p-4 bg-white">
                <form action="update_profile.php" method="POST" novalidate>
                    <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo $fullName; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $phone; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo $location; ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="5" required><?php echo $bio; ?></textarea>
                            <div class="form-text">Tell employers about your goals, experience, and skills.</div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-3">
                        <button type="submit" class="btn btn-primary-custom">Save Changes</button>
                        <a href="profile.php" class="btn btn-outline-custom">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
