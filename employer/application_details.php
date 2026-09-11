<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('employer');
$pageTitle = 'Application Review';
$conn = $GLOBALS['conn'];
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

$employerStmt = $conn->prepare('SELECT company_id FROM employers WHERE user_id = ? LIMIT 1');
$employerStmt->bind_param('i', $userId);
$employerStmt->execute();
$employerResult = $employerStmt->get_result();
$employer = $employerResult->fetch_assoc();
$employerStmt->close();

if (!$employer || empty($employer['company_id'])) {
    $_SESSION['error'] = 'Please complete your company profile before reviewing applicants.';
    redirect('profile.php');
}

$companyId = (int) $employer['company_id'];
$applicationId = isset($_GET['application_id']) ? (int) $_GET['application_id'] : 0;

if ($applicationId <= 0) {
    $_SESSION['error'] = 'Invalid application reference.';
    redirect('applicants.php');
}

$applicationStmt = $conn->prepare(
    'SELECT a.*, j.title, j.description, c.company_name, u.full_name AS applicant_name, u.email, u.phone
     FROM applications a
     INNER JOIN jobs j ON a.job_id = j.job_id
     LEFT JOIN companies c ON j.company_id = c.company_id
     INNER JOIN graduates g ON a.graduate_id = g.graduate_id
     INNER JOIN users u ON g.user_id = u.user_id
     WHERE a.application_id = ? AND j.company_id = ?
     LIMIT 1'
);
$applicationStmt->bind_param('ii', $applicationId, $companyId);
$applicationStmt->execute();
$applicationResult = $applicationStmt->get_result();
$application = $applicationResult->fetch_assoc();
$applicationStmt->close();

if (!$application) {
    $_SESSION['error'] = 'You do not have access to that application.';
    redirect('applicants.php');
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
                        <h1 class="h4 fw-bold mb-1">Application Review</h1>
                        <p class="text-muted mb-0">Assess the candidate and update their application status.</p>
                    </div>
                    <a href="applicants.php" class="btn btn-outline-custom">Back to Applicants</a>
                </div>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Candidate Details</h2>
                            <p class="mb-2"><strong>Name:</strong> <?php echo htmlspecialchars($application['applicant_name'] ?: 'Not available'); ?></p>
                            <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($application['email'] ?: 'Not available'); ?></p>
                            <p class="mb-2"><strong>Phone:</strong> <?php echo htmlspecialchars($application['phone'] ?: 'Not available'); ?></p>
                            <p class="mb-0"><strong>Applied For:</strong> <?php echo htmlspecialchars($application['title']); ?></p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Application Status</h2>
                            <form method="post" action="update_application_status.php">
                                <input type="hidden" name="application_id" value="<?php echo (int) $application['application_id']; ?>">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Current Status</label>
                                    <select class="form-select" id="application_status" name="status">
                                        <option value="pending"<?php echo normalizeApplicationStatus($application['status'] ?? 'pending') === 'pending' ? ' selected' : ''; ?>>Pending</option>
                                        <option value="under_review"<?php echo normalizeApplicationStatus($application['status'] ?? 'pending') === 'under_review' ? ' selected' : ''; ?>>Under Review</option>
                                        <option value="shortlisted"<?php echo normalizeApplicationStatus($application['status'] ?? 'pending') === 'shortlisted' ? ' selected' : ''; ?>>Shortlisted</option>
                                        <option value="interview_scheduled"<?php echo normalizeApplicationStatus($application['status'] ?? 'pending') === 'interview_scheduled' ? ' selected' : ''; ?>>Interview Scheduled</option>
                                        <option value="accepted"<?php echo normalizeApplicationStatus($application['status'] ?? 'pending') === 'accepted' ? ' selected' : ''; ?>>Accepted</option>
                                        <option value="rejected"<?php echo normalizeApplicationStatus($application['status'] ?? 'pending') === 'rejected' ? ' selected' : ''; ?>>Rejected</option>
                                    </select>
                                </div>
                                <div class="row g-3 mb-3" id="interview-fields" hidden>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="interview_date">Interview Date</label>
                                        <input type="date" class="form-control" id="interview_date" name="interview_date" value="<?php echo htmlspecialchars($application['interview_date'] ?? ''); ?>" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="interview_time">Interview Time</label>
                                        <input type="time" class="form-control" id="interview_time" name="interview_time" value="<?php echo htmlspecialchars($application['interview_time'] ?? ''); ?>" disabled>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary-custom">Update Status</button>
                            </form>
                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const statusSelect = document.getElementById('application_status');
                                    const interviewFields = document.getElementById('interview-fields');
                                    const interviewDate = document.getElementById('interview_date');
                                    const interviewTime = document.getElementById('interview_time');

                                    function toggleInterviewFields() {
                                        const isInterviewScheduled = statusSelect.value === 'interview_scheduled';
                                        interviewFields.hidden = !isInterviewScheduled;
                                        interviewDate.required = isInterviewScheduled;
                                        interviewTime.required = isInterviewScheduled;
                                        interviewDate.disabled = !isInterviewScheduled;
                                        interviewTime.disabled = !isInterviewScheduled;
                                    }

                                    statusSelect.addEventListener('change', toggleInterviewFields);
                                    toggleInterviewFields();
                                });
                            </script>
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
                        <a href="../graduate/download_cv.php?file=<?php echo urlencode($application['cv_path']); ?>" class="btn btn-outline-custom">Download CV</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
