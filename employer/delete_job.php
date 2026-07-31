<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('employer');
$conn = $GLOBALS['conn'];
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

$employerStmt = $conn->prepare('SELECT company_id FROM employers WHERE user_id = ? LIMIT 1');
$employerStmt->bind_param('i', $userId);
$employerStmt->execute();
$employerResult = $employerStmt->get_result();
$employer = $employerResult->fetch_assoc();
$employerStmt->close();

$companyId = $employer['company_id'] ?? null;
if (!$companyId) {
    $_SESSION['error'] = 'Unable to delete jobs without a linked company profile.';
    redirect('profile.php');
}

$jobId = $_GET['job_id'] ?? $_POST['job_id'] ?? null;
if (!ctype_digit((string) $jobId)) {
    $_SESSION['error'] = 'Invalid job ID.';
    redirect('manage_jobs.php');
}

$jobId = (int) $jobId;

$jobStmt = $conn->prepare(
    'SELECT job_id, title FROM jobs WHERE job_id = ? AND company_id = ? LIMIT 1'
);
$jobStmt->bind_param('ii', $jobId, $companyId);
$jobStmt->execute();
$jobResult = $jobStmt->get_result();
$job = $jobResult->fetch_assoc();
$jobStmt->close();

if (!$job) {
    $_SESSION['error'] = 'The selected job was not found or you do not have permission to delete it.';
    redirect('manage_jobs.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirmDelete = isset($_POST['confirm_delete']) ? (int) $_POST['confirm_delete'] : 0;
    if ($confirmDelete !== 1) {
        $_SESSION['error'] = 'Deletion was not confirmed.';
        redirect('manage_jobs.php');
    }

    $conn->begin_transaction();
    try {
        $deleteStmt = $conn->prepare('DELETE FROM jobs WHERE job_id = ? AND company_id = ? LIMIT 1');
        $deleteStmt->bind_param('ii', $jobId, $companyId);
        if (!$deleteStmt->execute() || $deleteStmt->affected_rows < 1) {
            throw new Exception('Unable to delete job posting.');
        }
        $deleteStmt->close();
        $conn->commit();

        $_SESSION['success'] = 'Job deleted successfully.';
        redirect('manage_jobs.php');
    } catch (Exception $e) {
        $conn->rollback();
        error_log('Job deletion failed: ' . $e->getMessage());
        $_SESSION['error'] = 'Unable to delete the job at this time. Please try again later.';
        redirect('manage_jobs.php');
    }
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
                <h1 class="h4 fw-bold mb-2">Delete Job</h1>
                <p class="text-muted mb-4">This action cannot be undone.</p>

                <div class="border rounded-4 p-4 bg-light mb-4">
                    <h2 class="h6 fw-semibold mb-2">Are you sure you want to delete this job?</h2>
                    <p class="mb-0 fw-semibold">"<?php echo htmlspecialchars($job['title']); ?>"</p>
                </div>

                <form method="post" action="delete_job.php">
                    <input type="hidden" name="job_id" value="<?php echo (int) $job['job_id']; ?>">
                    <input type="hidden" name="confirm_delete" value="1">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="manage_jobs.php" class="btn btn-outline-custom">Cancel</a>
                        <button type="submit" class="btn btn-danger">Delete Job</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
