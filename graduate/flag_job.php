<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');
$pageTitle = 'Report Job Posting';
$conn = $GLOBALS['conn'];
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

$jobId = $_GET['job_id'] ?? null;
if (!ctype_digit((string) $jobId)) {
    $_SESSION['error'] = 'Invalid job selection.';
    redirect('jobs.php');
}

$jobId = (int) $jobId;
$jobStmt = $conn->prepare('SELECT job_id, title FROM jobs WHERE job_id = ? LIMIT 1');
$jobStmt->bind_param('i', $jobId);
$jobStmt->execute();
$jobResult = $jobStmt->get_result();
$job = $jobResult->fetch_assoc();
$jobStmt->close();

if (!$job) {
    $_SESSION['error'] = 'The selected job could not be found.';
    redirect('jobs.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = trim($_POST['reason'] ?? '');

    if ($reason === '') {
        $_SESSION['error'] = 'Please provide a reason for reporting this job.';
        redirect('flag_job.php?job_id=' . $jobId);
    }

    flagJobForModeration($conn, $jobId, $reason);

    $_SESSION['success'] = 'The job has been reported. Administrators will review it shortly.';
    redirect('job_details.php?job_id=' . $jobId);
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
                        <h1 class="h3 fw-bold mb-2">Report Job Posting</h1>
                        <p class="text-muted mb-0">Report any job posting that appears inappropriate or misleading.</p>
                    </div>
                    <a href="job_details.php?job_id=<?php echo (int) $job['job_id']; ?>" class="btn btn-outline-custom">Back to Job</a>
                </div>

                <div class="border rounded-4 p-3 bg-light mb-4">
                    <p class="fw-semibold mb-1"><?php echo htmlspecialchars($job['title']); ?></p>
                    <p class="text-muted mb-0">Please share the reason for your report so administrators can investigate.</p>
                </div>

                <form method="post" class="row g-3">
                    <input type="hidden" name="job_id" value="<?php echo (int) $job['job_id']; ?>">

                    <div class="col-12">
                        <label for="reason" class="form-label fw-semibold">Reason for Report</label>
                        <textarea id="reason" name="reason" class="form-control" rows="6" required placeholder="Explain why this job should be reviewed by an administrator."></textarea>
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-danger">Submit Report</button>
                        <a href="job_details.php?job_id=<?php echo (int) $job['job_id']; ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>