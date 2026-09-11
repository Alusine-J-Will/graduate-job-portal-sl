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
    $_SESSION['error'] = 'Unable to view job details without a linked company profile.';
    redirect('profile.php');
}

$jobId = $_GET['job_id'] ?? $_GET['id'] ?? null;
if (!ctype_digit((string) $jobId)) {
    $_SESSION['error'] = 'Invalid job ID.';
    redirect('manage_jobs.php');
}

$jobId = (int) $jobId;

$jobStmt = $conn->prepare(
    'SELECT j.job_id, j.company_id, j.category_id, j.title, j.description, j.requirements, j.location, j.employment_type, j.experience_level, j.work_mode, j.salary, j.vacancies, j.education_level, j.skills, j.responsibilities, j.benefits, j.deadline, j.status, j.created_at, j.updated_at, c.company_name, c.industry, c.location AS company_location, c.website, jc.category_name
     FROM jobs j
     LEFT JOIN companies c ON j.company_id = c.company_id
     LEFT JOIN job_categories jc ON j.category_id = jc.category_id
     WHERE j.job_id = ? AND j.company_id = ?
     LIMIT 1'
);
$jobStmt->bind_param('ii', $jobId, $companyId);
$jobStmt->execute();
$jobResult = $jobStmt->get_result();
$job = $jobResult->fetch_assoc();
$jobStmt->close();

if (!$job) {
    $_SESSION['error'] = 'The selected job was not found or you do not have permission to view it.';
    redirect('manage_jobs.php');
}

$deadline = !empty($job['deadline']) ? strtotime($job['deadline']) : null;
$today = strtotime(date('Y-m-d'));
$deadlineStatus = 'Active';
$deadlineBadgeClass = 'bg-success';

if ($deadline !== null) {
    if ($deadline < $today) {
        $deadlineStatus = 'Expired';
        $deadlineBadgeClass = 'bg-danger';
    } elseif ($deadline <= strtotime('+7 days', $today)) {
        $deadlineStatus = 'Closing Soon';
        $deadlineBadgeClass = 'bg-warning text-dark';
    }
}

$statusBadgeClass = 'secondary';
if ($job['status'] === 'Open') {
    $statusBadgeClass = 'success';
} elseif ($job['status'] === 'Closed') {
    $statusBadgeClass = 'warning text-dark';
} elseif ($job['status'] === 'Expired') {
    $statusBadgeClass = 'danger';
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
                        <h1 class="h3 fw-bold mb-2"><?php echo htmlspecialchars($job['title']); ?></h1>
                        <p class="text-muted mb-0">Preview how this job posting appears to graduates.</p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-<?php echo $statusBadgeClass; ?> fs-6"><?php echo htmlspecialchars($job['status'] ?? 'Draft'); ?></span>
                        <span class="badge <?php echo $deadlineBadgeClass; ?> fs-6"><?php echo htmlspecialchars($deadlineStatus); ?></span>
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap mb-4">
                    <a href="manage_jobs.php" class="btn btn-outline-custom">Back to Manage Jobs</a>
                    <a href="edit_job.php?job_id=<?php echo (int) $job['job_id']; ?>" class="btn btn-outline-secondary">Edit Job</a>
                    <a href="delete_job.php?job_id=<?php echo (int) $job['job_id']; ?>" class="btn btn-outline-danger">Delete Job</a>
                </div>

                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h2 class="h6 fw-semibold mb-3">Company Information</h2>
                                <p class="mb-2"><strong>Company:</strong> <?php echo htmlspecialchars($job['company_name'] ?: 'Not available'); ?></p>
                                <p class="mb-2"><strong>Industry:</strong> <?php echo htmlspecialchars($job['industry'] ?: 'Not available'); ?></p>
                                <p class="mb-2"><strong>Location:</strong> <?php echo htmlspecialchars($job['company_location'] ?: 'Not available'); ?></p>
                                <p class="mb-0"><strong>Website:</strong> <?php echo !empty($job['website']) ? '<a href="' . htmlspecialchars($job['website']) . '" target="_blank" rel="noopener">' . htmlspecialchars($job['website']) . '</a>' : 'Not available'; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h2 class="h6 fw-semibold mb-3">Job Overview</h2>
                                <div class="row g-3">
                                    <div class="col-md-6"><strong>Category:</strong> <?php echo htmlspecialchars($job['category_name'] ?: 'Uncategorized'); ?></div>
                                    <div class="col-md-6"><strong>Location:</strong> <?php echo htmlspecialchars($job['location'] ?: 'Not provided'); ?></div>
                                    <div class="col-md-6"><strong>Employment Type:</strong> <?php echo htmlspecialchars($job['employment_type'] ?: 'Not provided'); ?></div>
                                    <div class="col-md-6"><strong>Work Mode:</strong> <?php echo htmlspecialchars($job['work_mode'] ?: 'Not provided'); ?></div>
                                    <div class="col-md-6"><strong>Experience Level:</strong> <?php echo htmlspecialchars($job['experience_level'] ?: 'Not provided'); ?></div>
                                    <div class="col-md-6"><strong>Education Level:</strong> <?php echo htmlspecialchars($job['education_level'] ?: 'Not provided'); ?></div>
                                    <div class="col-md-6"><strong>Salary:</strong> <?php echo htmlspecialchars(formatJobSalaryDisplay($job['salary'] ?? null, $job['salary_type'] ?? null, $job['salary_amount'] ?? null, $job['salary_period'] ?? null)); ?></div>
                                    <div class="col-md-6"><strong>Vacancies:</strong> <?php echo htmlspecialchars((string) ($job['vacancies'] ?? 'Not specified')); ?></div>
                                    <div class="col-md-6"><strong>Deadline:</strong> <?php echo !empty($job['deadline']) ? date('d M Y', strtotime($job['deadline'])) : 'Not specified'; ?></div>
                                    <div class="col-md-6"><strong>Date Posted:</strong> <?php echo !empty($job['created_at']) ? date('d M Y', strtotime($job['created_at'])) : 'Not available'; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h2 class="h6 fw-semibold mb-3">Job Description</h2>
                                <div class="text-muted" style="white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($job['description'] ?: 'No description provided.')); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h2 class="h6 fw-semibold mb-3">Responsibilities</h2>
                                <div class="text-muted" style="white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($job['responsibilities'] ?: 'No responsibilities provided.')); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h2 class="h6 fw-semibold mb-3">Requirements</h2>
                                <div class="text-muted" style="white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($job['requirements'] ?: 'No requirements provided.')); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h2 class="h6 fw-semibold mb-3">Skills</h2>
                                <div class="text-muted" style="white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($job['skills'] ?: 'No skills provided.')); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h2 class="h6 fw-semibold mb-3">Benefits</h2>
                                <div class="text-muted" style="white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($job['benefits'] ?: 'No benefits provided.')); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
