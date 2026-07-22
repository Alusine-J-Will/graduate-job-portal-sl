<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');
$pageTitle = 'My Profile';

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$conn = $GLOBALS['conn'];

$userStmt = $conn->prepare('SELECT full_name, email, phone FROM users WHERE user_id = ? LIMIT 1');
$userStmt->bind_param('i', $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();
$userStmt->close();

$graduateStmt = $conn->prepare('SELECT graduate_id, location, bio, cv, profile_picture FROM graduates WHERE user_id = ? LIMIT 1');
$graduateStmt->bind_param('i', $userId);
$graduateStmt->execute();
$graduateResult = $graduateStmt->get_result();
$graduate = $graduateResult->fetch_assoc();
$graduateStmt->close();

$graduateId = $graduate['graduate_id'] ?? null;
$educationCount = 0;
$experienceCount = 0;
$skillCount = 0;

if ($graduateId) {
    $countStmt = $conn->prepare('SELECT COUNT(*) AS total FROM education WHERE graduate_id = ?');
    $countStmt->bind_param('i', $graduateId);
    $countStmt->execute();
    $countStmt->bind_result($educationCount);
    $countStmt->fetch();
    $countStmt->close();

    $countStmt = $conn->prepare('SELECT COUNT(*) AS total FROM experience WHERE graduate_id = ?');
    $countStmt->bind_param('i', $graduateId);
    $countStmt->execute();
    $countStmt->bind_result($experienceCount);
    $countStmt->fetch();
    $countStmt->close();

    $countStmt = $conn->prepare('SELECT COUNT(*) AS total FROM graduate_skills WHERE graduate_id = ?');
    $countStmt->bind_param('i', $graduateId);
    $countStmt->execute();
    $countStmt->bind_result($skillCount);
    $countStmt->fetch();
    $countStmt->close();
}

$profileStatus = [
    'personal' => !empty($user['full_name']) && !empty($user['email']) && !empty($user['phone']),
    'photo' => !empty($graduate['profile_picture']),
    'location' => !empty($graduate['location']),
    'bio' => !empty($graduate['bio']),
    'education' => $educationCount > 0,
    'experience' => $experienceCount > 0,
    'skills' => $skillCount > 0,
    'cv' => !empty($graduate['cv']),
];

$completionPercentage = calculateProfileCompletion($profileStatus);
$avatarSrc = !empty($graduate['profile_picture']) ? BASE_URL . 'uploads/profile_photos/' . htmlspecialchars($graduate['profile_picture']) : null;
$dashboardAvatar = $graduate['profile_picture'] ?? null;
$cvExists = !empty($graduate['cv']);
$cvFile = $cvExists ? htmlspecialchars($graduate['cv']) : null;
$locationText = !empty($graduate['location']) ? htmlspecialchars($graduate['location']) : 'Location not added.';
$bioText = !empty($graduate['bio']) ? nl2br(htmlspecialchars($graduate['bio'])) : 'Add a bio to tell employers more about yourself.';

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
                        <h2 class="h5 fw-semibold mb-1">Personal Information</h2>
                        <p class="text-muted mb-0">Review and manage your graduate profile details.</p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="edit_profile.php" class="btn btn-primary-custom">Edit Profile</a>
                        <a href="upload_photo.php" class="btn btn-outline-custom">Upload Photo</a>
                        <a href="upload_cv.php" class="btn btn-outline-custom">Upload CV</a>
                    </div>
                </div>
            </div>

            <div class="card-ui p-4 mb-4 bg-white">
                <div class="d-flex flex-column flex-md-row align-items-start justify-content-between gap-3">
                    <div>
                        <h3 class="h6 fw-semibold mb-2">Profile Sections</h3>
                        <p class="text-muted mb-0">Quick access to your education, experience, and skills sections.</p>
                    </div>
                </div>
                <div class="row g-3 mt-3">
                    <div class="col-md-4">
                        <a href="education.php" class="card-ui h-100 p-3 text-decoration-none text-dark border rounded-4 hover-border-primary d-block">
                            <div class="d-flex align-items-center gap-3">
                                <i class="fas fa-graduation-cap fa-2x text-primary"></i>
                                <div>
                                    <h4 class="h6 mb-1">Education</h4>
                                    <p class="small text-muted mb-0">Manage your qualifications and academic history.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="experience.php" class="card-ui h-100 p-3 text-decoration-none text-dark border rounded-4 hover-border-primary d-block">
                            <div class="d-flex align-items-center gap-3">
                                <i class="fas fa-briefcase fa-2x text-primary"></i>
                                <div>
                                    <h4 class="h6 mb-1">Experience</h4>
                                    <p class="small text-muted mb-0">Add your work history and professional roles.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="skills.php" class="card-ui h-100 p-3 text-decoration-none text-dark border rounded-4 hover-border-primary d-block">
                            <div class="d-flex align-items-center gap-3">
                                <i class="fas fa-tags fa-2x text-primary"></i>
                                <div>
                                    <h4 class="h6 mb-1">Skills</h4>
                                    <p class="small text-muted mb-0">Showcase your technical and soft skills.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-xl-8">
                    <div class="card-ui p-4 bg-white">
                        <div class="d-flex flex-column flex-md-row align-items-center gap-4 mb-4">
                            <a href="upload_photo.php" class="profile-avatar-wrapper d-inline-block text-decoration-none">
                                <?php if ($avatarSrc): ?>
                                    <img src="<?php echo $avatarSrc; ?>" alt="Profile photo" class="profile-avatar rounded-circle shadow-sm">
                                <?php else: ?>
                                    <div class="profile-avatar default-avatar rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                                        <i class="fas fa-user fa-2x text-white"></i>
                                    </div>
                                <?php endif; ?>
                                <span class="visually-hidden">Upload profile photo</span>
                            </a>
                            <div>
                                <h3 class="h5 mb-1"><?php echo htmlspecialchars($user['full_name'] ?? 'Graduate'); ?></h3>
                                <p class="text-muted mb-1"><?php echo htmlspecialchars($user['email'] ?? 'Email not provided'); ?></p>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($user['phone'] ?? 'Phone not provided'); ?></p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 bg-light">
                                    <p class="text-uppercase small text-muted mb-2">Location</p>
                                    <p class="mb-0"><?php echo $locationText; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 bg-light">
                                    <p class="text-uppercase small text-muted mb-2">CV</p>
                                    <?php if ($cvExists): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-success">Uploaded</span>
                                            <a href="download_cv.php?file=<?php echo urlencode($cvFile); ?>" class="btn btn-sm btn-outline-custom">Download</a>
                                        </div>
                                    <?php else: ?>
                                        <p class="mb-0 text-muted">No CV uploaded yet.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <p class="text-uppercase small text-muted mb-2">About Me</p>
                            <div class="border rounded-4 p-3 bg-light">
                                <?php echo $bioText; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <h3 class="h6 fw-semibold mb-3">Profile Completion</h3>
                        <div class="progress mb-3" style="height: 12px; border-radius: 999px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $completionPercentage; ?>%;" aria-valuenow="<?php echo $completionPercentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <p class="mb-3 text-muted"><?php echo $completionPercentage; ?>% complete</p>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="fas fa-user-check text-primary me-2"></i>Personal info</li>
                            <li class="mb-2"><i class="fas fa-camera text-primary me-2"></i>Profile photo</li>
                            <li class="mb-2"><i class="fas fa-map-marker-alt text-primary me-2"></i>Location</li>
                            <li class="mb-2"><i class="fas fa-comment text-primary me-2"></i>Bio</li>
                            <li class="mb-2"><i class="fas fa-graduation-cap text-primary me-2"></i>Education</li>
                            <li class="mb-2"><i class="fas fa-briefcase text-primary me-2"></i>Experience</li>
                            <li class="mb-2"><i class="fas fa-tags text-primary me-2"></i>Skills</li>
                            <li><i class="fas fa-file-pdf text-primary me-2"></i>CV</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
