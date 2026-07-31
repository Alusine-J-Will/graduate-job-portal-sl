<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please sign in with an administrator account.';
    redirect(BASE_URL . 'auth/login.php');
}

$pageTitle = 'Delete Job';
$conn = $GLOBALS['conn'];

$jobId = isset($_GET['job_id']) ? (int) $_GET['job_id'] : 0;
if ($jobId <= 0) {
    $_SESSION['error'] = 'Invalid job reference.';
    redirect('jobs.php');
}

$jobStmt = $conn->prepare('SELECT job_id, title FROM jobs WHERE job_id = ? LIMIT 1');
$jobStmt->bind_param('i', $jobId);
$jobStmt->execute();
$jobResult = $jobStmt->get_result();
$job = $jobResult->fetch_assoc();
$jobStmt->close();

if (!$job) {
    $_SESSION['error'] = 'Job not found.';
    redirect('jobs.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirmDelete = isset($_POST['confirm_delete']) ? trim($_POST['confirm_delete']) : '';
    if ($confirmDelete !== 'yes') {
        $_SESSION['error'] = 'Deletion cancelled.';
        redirect('job_details.php?job_id=' . $jobId);
    }

    $conn->begin_transaction();

    $deleteApplicationsStmt = $conn->prepare('DELETE FROM applications WHERE job_id = ?');
    $deleteApplicationsStmt->bind_param('i', $jobId);
    $deleteApplicationsStmt->execute();
    $deleteApplicationsStmt->close();

    $deleteSavedJobsStmt = $conn->prepare('DELETE FROM saved_jobs WHERE job_id = ?');
    $deleteSavedJobsStmt->bind_param('i', $jobId);
    $deleteSavedJobsStmt->execute();
    $deleteSavedJobsStmt->close();

    $deleteJobStmt = $conn->prepare('DELETE FROM jobs WHERE job_id = ?');
    $deleteJobStmt->bind_param('i', $jobId);
    $deleteJobStmt->execute();
    $deleteJobStmt->close();

    if ($conn->errno === 0) {
        $conn->commit();
        $_SESSION['success'] = 'Job deleted successfully.';
        redirect('jobs.php');
    }

    $conn->rollback();
    $_SESSION['error'] = 'Unable to delete the job. Please try again.';
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

            <div class="card-ui p-4 bg-white">
                <h1 class="h4 fw-bold mb-3">Delete Job</h1>
                <p class="text-muted">You are about to delete the job <strong><?php echo htmlspecialchars($job['title']); ?></strong>. This will remove related applications and saved-job records.</p>
                <p class="text-danger fw-semibold">This action cannot be undone.</p>

                <form method="post" class="d-flex gap-2 mt-4">
                    <input type="hidden" name="confirm_delete" value="yes">
                    <button type="submit" class="btn btn-danger">Confirm Delete</button>
                    <a href="job_details.php?job_id=<?php echo (int) $job['job_id']; ?>" class="btn btn-outline-custom">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
