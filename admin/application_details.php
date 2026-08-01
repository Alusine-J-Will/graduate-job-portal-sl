<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please sign in with an administrator account.';
    redirect(BASE_URL . 'auth/login.php');
}

$pageTitle = 'Application Details';
$conn = $GLOBALS['conn'];

$applicationId = isset($_GET['application_id']) ? (int) $_GET['application_id'] : 0;
if ($applicationId <= 0) {
    $_SESSION['error'] = 'Invalid application reference.';
    redirect('applications.php');
}

$applicationStmt = $conn->prepare(
    'SELECT a.application_id, a.status, a.application_date, a.cover_letter_path, a.cv_path, a.cover_letter_text,
        u.full_name AS graduate_name, u.email AS graduate_email, u.phone AS graduate_phone,
        g.graduate_id,
        j.title AS job_title, j.location AS job_location, c.company_name,
        e.job_title AS employer_title, eu.full_name AS employer_name, eu.email AS employer_email
     FROM applications a
     INNER JOIN graduates g ON a.graduate_id = g.graduate_id
     INNER JOIN users u ON g.user_id = u.user_id
     INNER JOIN jobs j ON a.job_id = j.job_id
     LEFT JOIN companies c ON j.company_id = c.company_id
     LEFT JOIN employers e ON j.company_id = e.company_id
     LEFT JOIN users eu ON e.user_id = eu.user_id
     WHERE a.application_id = ?
     LIMIT 1'
);
$applicationStmt->bind_param('i', $applicationId);
$applicationStmt->execute();
$applicationResult = $applicationStmt->get_result();
$application = $applicationResult->fetch_assoc();
$applicationStmt->close();

if (!$application) {
    $_SESSION['error'] = 'Application not found.';
    redirect('applications.php');
}

$educationStmt = $conn->prepare(
    'SELECT institution, degree, field_of_study, graduation_year, grade
     FROM education
     WHERE graduate_id = ?
     ORDER BY graduation_year DESC, created_at DESC'
);
$educationStmt->bind_param('i', $application['graduate_id']);
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
$experienceStmt->bind_param('i', $application['graduate_id']);
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
$skillsStmt->bind_param('i', $application['graduate_id']);
$skillsStmt->execute();
$skillsResult = $skillsStmt->get_result();
$skills = $skillsResult->fetch_all(MYSQLI_ASSOC);
$skillsStmt->close();

$totalApplications = 0;
$pendingApplications = 0;
$reviewedApplications = 0;
$shortlistedApplications = 0;
$rejectedApplications = 0;
$acceptedApplications = 0;

$countStmt = $conn->prepare('SELECT COUNT(*) FROM applications');
$countStmt->execute();
$countStmt->bind_result($totalApplications);
$countStmt->fetch();
$countStmt->close();

$pendingStmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE status = 'Pending'");
$pendingStmt->execute();
$pendingStmt->bind_result($pendingApplications);
$pendingStmt->fetch();
$pendingStmt->close();

$reviewedStmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE status = 'Reviewed'");
$reviewedStmt->execute();
$reviewedStmt->bind_result($reviewedApplications);
$reviewedStmt->fetch();
$reviewedStmt->close();

$shortlistedStmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE status = 'Shortlisted'");
$shortlistedStmt->execute();
$shortlistedStmt->bind_result($shortlistedApplications);
$shortlistedStmt->fetch();
$shortlistedStmt->close();

$rejectedStmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE status = 'Rejected'");
$rejectedStmt->execute();
$rejectedStmt->bind_result($rejectedApplications);
$rejectedStmt->fetch();
$rejectedStmt->close();

$acceptedStmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE status = 'Accepted'");
$acceptedStmt->execute();
$acceptedStmt->bind_result($acceptedApplications);
$acceptedStmt->fetch();
$acceptedStmt->close();

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

            <div class="card-ui p-4 bg-white mb-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                    <div>
                        <h1 class="h4 fw-bold mb-1">Application Details</h1>
                        <p class="text-muted mb-0">Review the candidate profile and application details.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="applications.php" class="btn btn-outline-custom">Back to Applications</a>
                        <form method="post" action="update_application_status.php" class="d-inline">
                            <input type="hidden" name="application_id" value="<?php echo (int) $application['application_id']; ?>">
                            <input type="hidden" name="status" value="Reviewed">
                            <button type="submit" class="btn btn-info">Mark Reviewed</button>
                        </form>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Graduate Information</h2>
                            <p class="mb-2"><strong>Full Name:</strong> <?php echo htmlspecialchars($application['graduate_name']); ?></p>
                            <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($application['graduate_email']); ?></p>
                            <p class="mb-2"><strong>Phone:</strong> <?php echo htmlspecialchars($application['graduate_phone'] ?: 'N/A'); ?></p>
                            <p class="mb-2"><strong>Skills:</strong></p>
                            <?php if (empty($skills)): ?>
                                <p class="text-muted mb-0">No skills listed.</p>
                            <?php else: ?>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <?php foreach ($skills as $skill): ?>
                                        <span class="badge bg-light text-dark"><?php echo htmlspecialchars($skill['skill_name']); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <p class="mb-2"><strong>Education:</strong></p>
                            <?php if (empty($education)): ?>
                                <p class="text-muted mb-0">No education records.</p>
                            <?php else: ?>
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($education as $item): ?>
                                        <li class="mb-2"><strong><?php echo htmlspecialchars($item['degree'] ?: 'Degree not specified'); ?></strong> - <?php echo htmlspecialchars($item['institution']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <p class="mb-2"><strong>Experience:</strong></p>
                            <?php if (empty($experience)): ?>
                                <p class="text-muted mb-0">No experience records.</p>
                            <?php else: ?>
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($experience as $item): ?>
                                        <li class="mb-2"><strong><?php echo htmlspecialchars($item['position']); ?></strong> at <?php echo htmlspecialchars($item['organization']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Application Information</h2>
                            <p class="mb-2"><strong>Status:</strong> <span class="badge bg-primary"><?php echo htmlspecialchars($application['status'] ?? 'Pending'); ?></span></p>
                            <p class="mb-2"><strong>Application Date:</strong> <?php echo date('d M Y', strtotime($application['application_date'])); ?></p>
                            <p class="mb-2"><strong>Cover Letter:</strong></p>
                            <div class="text-muted" style="white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($application['cover_letter_text'] ?: 'No cover letter provided.')); ?></div>
                            <p class="mt-3 mb-2"><strong>CV:</strong></p>
                            <?php if (!empty($application['cv_path'])): ?>
                                <a href="../graduate/download_cv.php?file=<?php echo urlencode($application['cv_path']); ?>" class="btn btn-outline-custom">Download CV</a>
                            <?php else: ?>
                                <p class="text-muted mb-0">No CV uploaded.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Job Information</h2>
                            <p class="mb-2"><strong>Job Title:</strong> <?php echo htmlspecialchars($application['job_title']); ?></p>
                            <p class="mb-2"><strong>Company Name:</strong> <?php echo htmlspecialchars($application['company_name'] ?: 'N/A'); ?></p>
                            <p class="mb-0"><strong>Location:</strong> <?php echo htmlspecialchars($application['job_location'] ?: 'N/A'); ?></p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Application Statistics</h2>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 d-flex justify-content-between"><span>Total</span><strong><?php echo (int) $totalApplications; ?></strong></li>
                                <li class="list-group-item px-0 d-flex justify-content-between"><span>Pending</span><strong><?php echo (int) $pendingApplications; ?></strong></li>
                                <li class="list-group-item px-0 d-flex justify-content-between"><span>Reviewed</span><strong><?php echo (int) $reviewedApplications; ?></strong></li>
                                <li class="list-group-item px-0 d-flex justify-content-between"><span>Shortlisted</span><strong><?php echo (int) $shortlistedApplications; ?></strong></li>
                                <li class="list-group-item px-0 d-flex justify-content-between"><span>Rejected</span><strong><?php echo (int) $rejectedApplications; ?></strong></li>
                                <li class="list-group-item px-0 d-flex justify-content-between"><span>Accepted</span><strong><?php echo (int) $acceptedApplications; ?></strong></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="border rounded-4 p-4">
                    <h2 class="h6 fw-semibold mb-3">Admin Actions</h2>
                    <div class="d-flex flex-wrap gap-2">
                        <form method="post" action="update_application_status.php" class="d-inline">
                            <input type="hidden" name="application_id" value="<?php echo (int) $application['application_id']; ?>">
                            <input type="hidden" name="status" value="Reviewed">
                            <button type="submit" class="btn btn-outline-info">Reviewed</button>
                        </form>
                        <form method="post" action="update_application_status.php" class="d-inline">
                            <input type="hidden" name="application_id" value="<?php echo (int) $application['application_id']; ?>">
                            <input type="hidden" name="status" value="Shortlisted">
                            <button type="submit" class="btn btn-outline-primary">Shortlist</button>
                        </form>
                        <form method="post" action="update_application_status.php" class="d-inline">
                            <input type="hidden" name="application_id" value="<?php echo (int) $application['application_id']; ?>">
                            <input type="hidden" name="status" value="Rejected">
                            <button type="submit" class="btn btn-outline-danger">Reject</button>
                        </form>
                        <form method="post" action="update_application_status.php" class="d-inline">
                            <input type="hidden" name="application_id" value="<?php echo (int) $application['application_id']; ?>">
                            <input type="hidden" name="status" value="Accepted">
                            <button type="submit" class="btn btn-outline-success">Accept</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
