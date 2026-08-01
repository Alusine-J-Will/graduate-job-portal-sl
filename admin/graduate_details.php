<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please sign in with an administrator account.';
    redirect(BASE_URL . 'auth/login.php');
}

$pageTitle = 'Graduate Details';
$conn = $GLOBALS['conn'];

$userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
if ($userId <= 0) {
    $_SESSION['error'] = 'Invalid graduate reference.';
    redirect('graduates.php');
}

$graduateStmt = $conn->prepare(
    'SELECT u.user_id, u.full_name, u.email, u.phone, u.status, u.created_at,
        g.graduate_id, g.location, g.bio, g.cv, g.profile_picture
     FROM users u
     INNER JOIN graduates g ON u.user_id = g.user_id
     WHERE u.user_id = ? AND u.role = ?
     LIMIT 1'
);
$graduateRole = 'graduate';
$graduateStmt->bind_param('is', $userId, $graduateRole);
$graduateStmt->execute();
$graduateResult = $graduateStmt->get_result();
$graduate = $graduateResult->fetch_assoc();
$graduateStmt->close();

if (!$graduate) {
    $_SESSION['error'] = 'Graduate account not found.';
    redirect('graduates.php');
}

$educationStmt = $conn->prepare(
    'SELECT institution, degree, field_of_study, graduation_year, grade
     FROM education
     WHERE graduate_id = ?
     ORDER BY graduation_year DESC, created_at DESC'
);
$educationStmt->bind_param('i', $graduate['graduate_id']);
$educationStmt->execute();
$educationResult = $educationStmt->get_result();
$education = $educationResult->fetch_all(MYSQLI_ASSOC);
$educationStmt->close();

$experienceStmt = $conn->prepare(
    'SELECT organization, position, start_date, end_date, description
     FROM experience
     WHERE graduate_id = ?
     ORDER BY start_date DESC, created_at DESC'
);
$experienceStmt->bind_param('i', $graduate['graduate_id']);
$experienceStmt->execute();
$experienceResult = $experienceStmt->get_result();
$experience = $experienceResult->fetch_all(MYSQLI_ASSOC);
$experienceStmt->close();

$skillsStmt = $conn->prepare(
    'SELECT s.skill_name
     FROM graduate_skills gs
     INNER JOIN skills s ON gs.skill_id = s.skill_id
     WHERE gs.graduate_id = ?
     ORDER BY s.skill_name ASC'
);
$skillsStmt->bind_param('i', $graduate['graduate_id']);
$skillsStmt->execute();
$skillsResult = $skillsStmt->get_result();
$skills = $skillsResult->fetch_all(MYSQLI_ASSOC);
$skillsStmt->close();

$applicationsStmt = $conn->prepare(
    'SELECT application_id, status, application_date, updated_at
     FROM applications
     WHERE graduate_id = ?
     ORDER BY application_date DESC
     LIMIT 5'
);
$applicationsStmt->bind_param('i', $graduate['graduate_id']);
$applicationsStmt->execute();
$applicationsResult = $applicationsStmt->get_result();
$applications = $applicationsResult->fetch_all(MYSQLI_ASSOC);
$applicationsStmt->close();

$totalApplications = 0;
$pendingApplications = 0;
$acceptedApplications = 0;
$rejectedApplications = 0;

$countStmt = $conn->prepare('SELECT COUNT(*) FROM applications WHERE graduate_id = ?');
$countStmt->bind_param('i', $graduate['graduate_id']);
$countStmt->execute();
$countStmt->bind_result($totalApplications);
$countStmt->fetch();
$countStmt->close();

$pendingStmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE graduate_id = ? AND status NOT IN ('Accepted','Rejected')");
$pendingStmt->bind_param('i', $graduate['graduate_id']);
$pendingStmt->execute();
$pendingStmt->bind_result($pendingApplications);
$pendingStmt->fetch();
$pendingStmt->close();

$acceptedStmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE graduate_id = ? AND status = 'Accepted'");
$acceptedStmt->bind_param('i', $graduate['graduate_id']);
$acceptedStmt->execute();
$acceptedStmt->bind_result($acceptedApplications);
$acceptedStmt->fetch();
$acceptedStmt->close();

$rejectedStmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE graduate_id = ? AND status = 'Rejected'");
$rejectedStmt->bind_param('i', $graduate['graduate_id']);
$rejectedStmt->execute();
$rejectedStmt->bind_result($rejectedApplications);
$rejectedStmt->fetch();
$rejectedStmt->close();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/dashboard_topbar.php';
?>

<div class="container-fluid py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        </div>
        <div class="col-lg-9">
            <?php displayFlashMessages(); ?>

            <div class="card-ui p-4 bg-white mb-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                    <div>
                        <h1 class="h4 fw-bold mb-1">Graduate Details</h1>
                        <p class="text-muted mb-0">Review graduate profile details and application activity.</p>
                    </div>
                    <a href="graduates.php" class="btn btn-outline-custom">Back to Graduates</a>
                </div>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Account Information</h2>
                            <p class="mb-2"><strong>Full Name:</strong> <?php echo htmlspecialchars($graduate['full_name']); ?></p>
                            <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($graduate['email']); ?></p>
                            <p class="mb-2"><strong>Phone:</strong> <?php echo htmlspecialchars($graduate['phone'] ?: 'N/A'); ?></p>
                            <p class="mb-2"><strong>Status:</strong> <span class="badge bg-success"><?php echo htmlspecialchars(ucfirst($graduate['status'] ?? 'pending')); ?></span></p>
                            <p class="mb-0"><strong>Registration Date:</strong> <?php echo date('d M Y', strtotime($graduate['created_at'])); ?></p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Profile Information</h2>
                            <?php if (!empty($graduate['profile_picture'])): ?>
                                <img src="<?php echo BASE_URL; ?>uploads/profile_photos/<?php echo htmlspecialchars($graduate['profile_picture']); ?>" alt="Profile photo" class="img-fluid rounded-4 mb-3" style="max-height: 180px;">
                            <?php else: ?>
                                <p class="text-muted">No profile photo uploaded.</p>
                            <?php endif; ?>
                            <p class="mb-2"><strong>Location:</strong> <?php echo htmlspecialchars($graduate['location'] ?: 'Not added'); ?></p>
                            <p class="mb-2"><strong>Bio:</strong> <?php echo nl2br(htmlspecialchars($graduate['bio'] ?: 'Not added')); ?></p>
                            <?php if (!empty($graduate['cv'])): ?>
                                <a href="<?php echo BASE_URL; ?>graduate/download_cv.php?file=<?php echo urlencode($graduate['cv']); ?>" class="btn btn-outline-custom mt-2">Download CV</a>
                            <?php else: ?>
                                <p class="text-muted mb-0">No CV uploaded.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Education</h2>
                            <?php if (empty($education)): ?>
                                <p class="text-muted mb-0">No education records found.</p>
                            <?php else: ?>
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($education as $item): ?>
                                        <li class="mb-2">
                                            <strong><?php echo htmlspecialchars($item['degree'] ?: 'Degree not specified'); ?></strong> - <?php echo htmlspecialchars($item['institution']); ?><br>
                                            <span class="text-muted small"><?php echo htmlspecialchars($item['field_of_study'] ?: 'Field not specified'); ?> • <?php echo htmlspecialchars($item['graduation_year'] ?: 'N/A'); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Experience</h2>
                            <?php if (empty($experience)): ?>
                                <p class="text-muted mb-0">No experience records found.</p>
                            <?php else: ?>
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($experience as $item): ?>
                                        <li class="mb-2">
                                            <strong><?php echo htmlspecialchars($item['position']); ?></strong> at <?php echo htmlspecialchars($item['organization']); ?><br>
                                            <span class="text-muted small"><?php echo htmlspecialchars($item['start_date'] ?: 'N/A'); ?> to <?php echo htmlspecialchars($item['end_date'] ?: 'Present'); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Skills</h2>
                            <?php if (empty($skills)): ?>
                                <p class="text-muted mb-0">No skills listed.</p>
                            <?php else: ?>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($skills as $skill): ?>
                                        <span class="badge bg-light text-dark"><?php echo htmlspecialchars($skill['skill_name']); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Application Statistics</h2>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 d-flex justify-content-between"><span>Total Applications</span><strong><?php echo (int) $totalApplications; ?></strong></li>
                                <li class="list-group-item px-0 d-flex justify-content-between"><span>Pending Applications</span><strong><?php echo (int) $pendingApplications; ?></strong></li>
                                <li class="list-group-item px-0 d-flex justify-content-between"><span>Accepted Applications</span><strong><?php echo (int) $acceptedApplications; ?></strong></li>
                                <li class="list-group-item px-0 d-flex justify-content-between"><span>Rejected Applications</span><strong><?php echo (int) $rejectedApplications; ?></strong></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mt-4 border rounded-4 p-4">
                    <h2 class="h6 fw-semibold mb-3">Recent Applications</h2>
                    <?php if (empty($applications)): ?>
                        <p class="text-muted mb-0">No applications found.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($applications as $application): ?>
                                <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="mb-1 fw-semibold">Application #<?php echo (int) $application['application_id']; ?></p>
                                        <p class="small text-muted mb-0">Status: <?php echo htmlspecialchars($application['status']); ?></p>
                                    </div>
                                    <span class="small text-muted"><?php echo date('d M Y', strtotime($application['application_date'])); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
