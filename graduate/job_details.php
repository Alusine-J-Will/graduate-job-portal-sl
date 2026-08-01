<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');
$pageTitle = 'Job Details';
$conn = $GLOBALS['conn'];
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

$graduateId = getGraduateIdByUserId($conn, $userId);
if (!$graduateId) {
    $_SESSION['error'] = 'Please complete your graduate profile before viewing job details.';
    redirect('profile.php');
}

$jobId = $_GET['job_id'] ?? null;
if (!ctype_digit((string) $jobId)) {
    $_SESSION['error'] = 'Invalid job ID.';
    redirect('jobs.php');
}

$jobId = (int) $jobId;

$jobStmt = $conn->prepare(
    'SELECT j.job_id, j.title, j.description, j.requirements, j.location, j.employment_type, j.experience_level, j.work_mode, j.salary, j.education_level, j.skills, j.responsibilities, j.benefits, j.deadline, j.status, j.created_at, c.company_name, c.industry, c.location AS company_location, c.website, jc.category_name,
     CASE WHEN sj.job_id IS NOT NULL THEN 1 ELSE 0 END AS is_saved
     FROM jobs j
     LEFT JOIN companies c ON j.company_id = c.company_id
     LEFT JOIN job_categories jc ON j.category_id = jc.category_id
     LEFT JOIN saved_jobs sj ON sj.job_id = j.job_id AND sj.graduate_id = ?
     WHERE j.job_id = ? AND j.status = ? AND (j.deadline IS NULL OR j.deadline >= CURDATE())
     LIMIT 1'
);
$jobStmt->bind_param('iis', $graduateId, $jobId, $statusOpen);
$statusOpen = 'Open';
$jobStmt->execute();
$jobResult = $jobStmt->get_result();
$job = $jobResult->fetch_assoc();
$jobStmt->close();

if (!$job) {
    $_SESSION['error'] = 'The selected job could not be found.';
    redirect('jobs.php');
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
                        <p class="text-muted mb-0"><?php echo htmlspecialchars($job['company_name'] ?: 'Company not listed'); ?></p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-success">Open</span>
                        <span class="badge bg-primary"><?php echo htmlspecialchars($job['category_name'] ?: 'Uncategorized'); ?></span>
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap mb-4">
                    <a href="jobs.php" class="btn btn-outline-custom">Back to Jobs</a>
                    <?php if ((int) $job['is_saved'] === 1): ?>
                        <button type="button" class="btn btn-outline-success" disabled>Saved</button>
                    <?php else: ?>
                        <form method="post" action="save_job.php" class="d-inline">
                            <input type="hidden" name="job_id" value="<?php echo (int) $job['job_id']; ?>">
                            <input type="hidden" name="redirect_to" value="job_details.php?job_id=<?php echo (int) $job['job_id']; ?>">
                            <button type="submit" class="btn btn-primary-custom">Save Job</button>
                        </form>
                    <?php endif; ?>
                    <a href="apply_job.php?job_id=<?php echo (int)$job['job_id']; ?>"
                        class="btn btn-primary-custom">
                        Apply Now
                    </a>                </div>

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
                                    <div class="col-md-6"><strong>Location:</strong> <?php echo htmlspecialchars($job['location'] ?: 'Not provided'); ?></div>
                                    <div class="col-md-6"><strong>Employment Type:</strong> <?php echo htmlspecialchars($job['employment_type'] ?: 'Not provided'); ?></div>
                                    <div class="col-md-6"><strong>Work Mode:</strong> <?php echo htmlspecialchars($job['work_mode'] ?: 'Not provided'); ?></div>
                                    <div class="col-md-6"><strong>Experience Level:</strong> <?php echo htmlspecialchars($job['experience_level'] ?: 'Not provided'); ?></div>
                                    <div class="col-md-6"><strong>Education Level:</strong> <?php echo htmlspecialchars($job['education_level'] ?: 'Not provided'); ?></div>
                                    <div class="col-md-6"><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary'] ?: 'Not specified'); ?></div>
                                    <div class="col-md-6"><strong>Deadline:</strong> <?php echo !empty($job['deadline']) ? date('d M Y', strtotime($job['deadline'])) : 'Not specified'; ?></div>
                                    <div class="col-md-6"><strong>Posted:</strong> <?php echo !empty($job['created_at']) ? date('d M Y', strtotime($job['created_at'])) : 'Not available'; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h2 class="h6 fw-semibold mb-3">Description</h2>
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
                                <?php if (!empty($job['skills'])): ?>
                                    <?php $skillItems = array_filter(array_map('trim', preg_split('/[,\n]+/', (string) $job['skills']))); ?>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($skillItems as $skill): ?>
                                            <span class="badge bg-light text-dark"><?php echo htmlspecialchars($skill); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted mb-0">No skills provided.</p>
                                <?php endif; ?>
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
