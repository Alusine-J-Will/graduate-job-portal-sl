<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please sign in with an administrator account.';
    redirect(BASE_URL . 'auth/login.php');
}

$pageTitle = 'Job Details';
$conn = $GLOBALS['conn'];

$jobId = isset($_GET['job_id']) ? (int) $_GET['job_id'] : 0;
if ($jobId <= 0) {
    $_SESSION['error'] = 'Invalid job reference.';
    redirect('jobs.php');
}

$jobStmt = $conn->prepare(
    'SELECT j.job_id, j.title, j.category_id, j.location, j.employment_type, j.work_mode, j.salary, j.vacancies, j.deadline, j.status,
        j.description, j.requirements, j.responsibilities, j.benefits, j.created_at,
        c.company_name, c.company_id,
        jc.category_name,
        e.employer_id,
        u.full_name AS employer_name,
        u.email AS employer_email
     FROM jobs j
     LEFT JOIN companies c ON j.company_id = c.company_id
     LEFT JOIN job_categories jc ON j.category_id = jc.category_id
     LEFT JOIN employers e ON j.company_id = e.company_id
     LEFT JOIN users u ON e.user_id = u.user_id
     WHERE j.job_id = ?
     LIMIT 1'
);
$jobStmt->bind_param('i', $jobId);
$jobStmt->execute();
$jobResult = $jobStmt->get_result();
$job = $jobResult->fetch_assoc();
$jobStmt->close();

if (!$job) {
    $_SESSION['error'] = 'Job not found.';
    redirect('jobs.php');
}

$totalApplications = 0;
$pendingApplications = 0;
$reviewedApplications = 0;
$shortlistedApplications = 0;
$rejectedApplications = 0;

$applicationsStmt = $conn->prepare('SELECT COUNT(*) FROM applications WHERE job_id = ?');
$applicationsStmt->bind_param('i', $jobId);
$applicationsStmt->execute();
$applicationsStmt->bind_result($totalApplications);
$applicationsStmt->fetch();
$applicationsStmt->close();

$pendingStmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE job_id = ? AND status = 'Submitted'");
$pendingStmt->bind_param('i', $jobId);
$pendingStmt->execute();
$pendingStmt->bind_result($pendingApplications);
$pendingStmt->fetch();
$pendingStmt->close();

$reviewedStmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE job_id = ? AND status IN ('Under Review', 'Interview', 'Accepted')");
$reviewedStmt->bind_param('i', $jobId);
$reviewedStmt->execute();
$reviewedStmt->bind_result($reviewedApplications);
$reviewedStmt->fetch();
$reviewedStmt->close();

$shortlistedStmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE job_id = ? AND status = 'Shortlisted'");
$shortlistedStmt->bind_param('i', $jobId);
$shortlistedStmt->execute();
$shortlistedStmt->bind_result($shortlistedApplications);
$shortlistedStmt->fetch();
$shortlistedStmt->close();

$rejectedStmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE job_id = ? AND status = 'Rejected'");
$rejectedStmt->bind_param('i', $jobId);
$rejectedStmt->execute();
$rejectedStmt->bind_result($rejectedApplications);
$rejectedStmt->fetch();
$rejectedStmt->close();

$applicantsStmt = $conn->prepare(
    'SELECT u.full_name, u.email, a.status, a.application_date
     FROM applications a
     INNER JOIN graduates g ON a.graduate_id = g.graduate_id
     INNER JOIN users u ON g.user_id = u.user_id
     WHERE a.job_id = ?
     ORDER BY a.application_date DESC
     LIMIT 10'
);
$applicantsStmt->bind_param('i', $jobId);
$applicantsStmt->execute();
$applicantsResult = $applicantsStmt->get_result();
$applicants = $applicantsResult->fetch_all(MYSQLI_ASSOC);
$applicantsStmt->close();

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
                        <h1 class="h4 fw-bold mb-1">Job Details</h1>
                        <p class="text-muted mb-0">Review the full job posting and its applicants.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="jobs.php" class="btn btn-outline-custom">Back to Jobs</a>
                        <form method="post" action="update_job_status.php" class="d-inline">
                            <input type="hidden" name="job_id" value="<?php echo (int) $job['job_id']; ?>">
                            <input type="hidden" name="status" value="Open">
                            <button type="submit" class="btn btn-success">Open Job</button>
                        </form>
                        <form method="post" action="update_job_status.php" class="d-inline">
                            <input type="hidden" name="job_id" value="<?php echo (int) $job['job_id']; ?>">
                            <input type="hidden" name="status" value="Closed">
                            <button type="submit" class="btn btn-secondary">Close Job</button>
                        </form>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-lg-7">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Job Information</h2>
                            <p class="mb-2"><strong>Title:</strong> <?php echo htmlspecialchars($job['title']); ?></p>
                            <p class="mb-2"><strong>Category:</strong> <?php echo htmlspecialchars($job['category_name'] ?: 'Uncategorized'); ?></p>
                            <p class="mb-2"><strong>Employment Type:</strong> <?php echo htmlspecialchars($job['employment_type'] ?: 'N/A'); ?></p>
                            <p class="mb-2"><strong>Work Mode:</strong> <?php echo htmlspecialchars($job['work_mode'] ?: 'N/A'); ?></p>
                            <p class="mb-2"><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                            <p class="mb-2"><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary'] ?: 'N/A'); ?></p>
                            <p class="mb-2"><strong>Vacancies:</strong> <?php echo (int) ($job['vacancies'] ?? 0); ?></p>
                            <p class="mb-2"><strong>Deadline:</strong> <?php echo !empty($job['deadline']) ? date('d M Y', strtotime($job['deadline'])) : 'N/A'; ?></p>
                            <p class="mb-0"><strong>Status:</strong> <span class="badge bg-success"><?php echo htmlspecialchars($job['status']); ?></span></p>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Company Information</h2>
                            <p class="mb-2"><strong>Company Name:</strong> <?php echo htmlspecialchars($job['company_name'] ?: 'N/A'); ?></p>
                            <p class="mb-2"><strong>Employer Name:</strong> <?php echo htmlspecialchars($job['employer_name'] ?: 'N/A'); ?></p>
                            <p class="mb-0"><strong>Employer Email:</strong> <?php echo htmlspecialchars($job['employer_email'] ?: 'N/A'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Job Description</h2>
                            <p class="mb-2"><strong>Description:</strong></p>
                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($job['description'] ?: 'No description provided.')); ?></p>
                            <p class="mb-2"><strong>Requirements:</strong></p>
                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($job['requirements'] ?: 'No requirements provided.')); ?></p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Responsibilities & Benefits</h2>
                            <p class="mb-2"><strong>Responsibilities:</strong></p>
                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($job['responsibilities'] ?: 'No responsibilities listed.')); ?></p>
                            <p class="mb-2"><strong>Benefits:</strong></p>
                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($job['benefits'] ?: 'No benefits listed.')); ?></p>
                        </div>
                    </div>
                </div>

                <div class="border rounded-4 p-4 mb-4">
                    <h2 class="h6 fw-semibold mb-3">Application Statistics</h2>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="border rounded-3 p-3">
                                <div class="small text-muted">Total Applications</div>
                                <div class="fw-bold fs-5"><?php echo (int) $totalApplications; ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded-3 p-3">
                                <div class="small text-muted">Pending</div>
                                <div class="fw-bold fs-5"><?php echo (int) $pendingApplications; ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded-3 p-3">
                                <div class="small text-muted">Reviewed</div>
                                <div class="fw-bold fs-5"><?php echo (int) $reviewedApplications; ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded-3 p-3">
                                <div class="small text-muted">Shortlisted</div>
                                <div class="fw-bold fs-5"><?php echo (int) $shortlistedApplications; ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded-3 p-3">
                                <div class="small text-muted">Rejected</div>
                                <div class="fw-bold fs-5"><?php echo (int) $rejectedApplications; ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border rounded-4 p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h6 fw-semibold mb-0">Change Job Status</h2>
                        <form method="post" action="update_job_status.php" class="d-flex gap-2">
                            <input type="hidden" name="job_id" value="<?php echo (int) $job['job_id']; ?>">
                            <select class="form-select" name="status" style="min-width: 150px;">
                                <option value="Open"<?php echo $job['status'] === 'Open' ? ' selected' : ''; ?>>Open</option>
                                <option value="Closed"<?php echo $job['status'] === 'Closed' ? ' selected' : ''; ?>>Closed</option>
                                <option value="Draft"<?php echo $job['status'] === 'Draft' ? ' selected' : ''; ?>>Draft</option>
                            </select>
                            <button type="submit" class="btn btn-primary-custom">Update</button>
                        </form>
                    </div>
                </div>

                <div class="border rounded-4 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h6 fw-semibold mb-0">Recent Applicants</h2>
                        <a href="delete_job.php?job_id=<?php echo (int) $job['job_id']; ?>" class="btn btn-outline-danger btn-sm">Delete Job</a>
                    </div>
                    <?php if (empty($applicants)): ?>
                        <p class="text-muted mb-0">No applicants yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Graduate Name</th>
                                        <th>Email</th>
                                        <th>Application Status</th>
                                        <th>Applied Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applicants as $applicant): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($applicant['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($applicant['email']); ?></td>
                                            <td><?php echo htmlspecialchars($applicant['status']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($applicant['application_date'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
