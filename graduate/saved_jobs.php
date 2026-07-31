<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');
$pageTitle = 'Saved Jobs';
$conn = $GLOBALS['conn'];
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

$graduateId = getGraduateIdByUserId($conn, $userId);
if (!$graduateId) {
    $_SESSION['error'] = 'Please complete your graduate profile before viewing saved jobs.';
    redirect('profile.php');
}

$savedStmt = $conn->prepare(
    'SELECT j.job_id, j.title, c.company_name, jc.category_name, j.location, j.employment_type, j.salary, j.deadline, sj.saved_at
     FROM saved_jobs sj
     INNER JOIN jobs j ON sj.job_id = j.job_id
     LEFT JOIN companies c ON j.company_id = c.company_id
     LEFT JOIN job_categories jc ON j.category_id = jc.category_id
     WHERE sj.graduate_id = ?
     ORDER BY sj.saved_at DESC'
);
$savedStmt->bind_param('i', $graduateId);
$savedStmt->execute();
$savedResult = $savedStmt->get_result();
$savedJobs = $savedResult->fetch_all(MYSQLI_ASSOC);
$savedStmt->close();

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
                        <h1 class="h4 fw-bold mb-1">Saved Jobs</h1>
                        <p class="text-muted mb-0">Track the roles you want to revisit later.</p>
                    </div>
                    <a href="jobs.php" class="btn btn-outline-custom">Browse More Jobs</a>
                </div>

                <?php if (empty($savedJobs)): ?>
                    <div class="alert alert-light text-center py-4 mb-0">You have not saved any jobs yet.</div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($savedJobs as $job): ?>
                            <div class="col-12">
                                <div class="border rounded-4 p-4">
                                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                                        <div>
                                            <h2 class="h5 fw-semibold mb-1"><?php echo htmlspecialchars($job['title']); ?></h2>
                                            <p class="text-muted mb-1"><?php echo htmlspecialchars($job['company_name'] ?: 'Company not listed'); ?></p>
                                            <p class="small text-muted mb-0"><?php echo htmlspecialchars($job['category_name'] ?: 'Uncategorized'); ?> • <?php echo htmlspecialchars($job['location']); ?></p>
                                        </div>
                                        <div class="text-md-end">
                                            <p class="small text-muted mb-1">Saved <?php echo date('d M Y', strtotime($job['saved_at'])); ?></p>
                                            <p class="small text-muted mb-0">Deadline <?php echo !empty($job['deadline']) ? date('d M Y', strtotime($job['deadline'])) : 'Open'; ?></p>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="job_details.php?job_id=<?php echo (int) $job['job_id']; ?>" class="btn btn-outline-custom">View Details</a>
                                        <form method="post" action="save_job.php" class="d-inline">
                                            <input type="hidden" name="job_id" value="<?php echo (int) $job['job_id']; ?>">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="redirect_to" value="saved_jobs.php">
                                            <button type="submit" class="btn btn-outline-danger">Remove</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
