<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');
$pageTitle = 'Application Details';
$conn = $GLOBALS['conn'];
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

$graduateStmt = $conn->prepare('SELECT graduate_id FROM graduates WHERE user_id = ? LIMIT 1');
$graduateStmt->bind_param('i', $userId);
$graduateStmt->execute();
$graduateResult = $graduateStmt->get_result();
$graduate = $graduateResult->fetch_assoc();
$graduateStmt->close();

if (!$graduate) {
    $_SESSION['error'] = 'Please complete your graduate profile before viewing application details.';
    redirect('profile.php');
}

$graduateId = (int) $graduate['graduate_id'];
$applicationId = isset($_GET['application_id']) ? (int) $_GET['application_id'] : 0;

if ($applicationId <= 0) {
    $_SESSION['error'] = 'Invalid application reference.';
    redirect('my_applications.php');
}

$applicationStmt = $conn->prepare(
    'SELECT a.*, j.title, j.description, c.company_name, u.full_name AS applicant_name, u.email, u.phone
     FROM applications a
     INNER JOIN jobs j ON a.job_id = j.job_id
     LEFT JOIN companies c ON j.company_id = c.company_id
     INNER JOIN graduates g ON a.graduate_id = g.graduate_id
     INNER JOIN users u ON g.user_id = u.user_id
     WHERE a.application_id = ? AND a.graduate_id = ?
     LIMIT 1'
);
$applicationStmt->bind_param('ii', $applicationId, $graduateId);
$applicationStmt->execute();
$applicationResult = $applicationStmt->get_result();
$application = $applicationResult->fetch_assoc();
$applicationStmt->close();

if (!$application) {
    $_SESSION['error'] = 'You do not have access to that application.';
    redirect('my_applications.php');
}

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
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h1 class="h4 fw-bold mb-1">Application Details</h1>
                        <p class="text-muted mb-0">Review your application for <?php echo htmlspecialchars($application['title']); ?>.</p>
                    </div>
                    <a href="my_applications.php" class="btn btn-outline-custom">Back to Applications</a>
                </div>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Application Information</h2>
                            <p class="mb-2"><strong>Job:</strong> <?php echo htmlspecialchars($application['title']); ?></p>
                            <p class="mb-2"><strong>Company:</strong> <?php echo htmlspecialchars($application['company_name'] ?: 'Company not listed'); ?></p>
                            <p class="mb-2"><strong>Applied:</strong> <?php echo date('d M Y', strtotime($application['application_date'])); ?></p>
                            <p class="mb-2"><strong>Status:</strong> <?php echo htmlspecialchars($application['status'] ?? 'Pending'); ?></p>
                            <p class="mb-0"><strong>Last Updated:</strong> <?php echo date('d M Y', strtotime($application['updated_at'])); ?></p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Your Profile</h2>
                            <p class="mb-2"><strong>Name:</strong> <?php echo htmlspecialchars($application['applicant_name'] ?: 'Not available'); ?></p>
                            <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($application['email'] ?: 'Not available'); ?></p>
                            <p class="mb-0"><strong>Phone:</strong> <?php echo htmlspecialchars($application['phone'] ?: 'Not available'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 border rounded-4 p-4">
                    <h2 class="h6 fw-semibold mb-3">Cover Letter</h2>
                    <div class="text-muted" style="white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($application['cover_letter_text'] ?: 'No cover letter was provided.')); ?></div>
                </div>

                <?php if (!empty($application['cv_path'])): ?>
                    <div class="mt-4 border rounded-4 p-4">
                        <h2 class="h6 fw-semibold mb-3">CV</h2>
                        <a href="download_cv.php?file=<?php echo urlencode($application['cv_path']); ?>" class="btn btn-outline-custom">Download CV</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
