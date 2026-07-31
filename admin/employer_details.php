<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please sign in with an administrator account.';
    redirect(BASE_URL . 'auth/login.php');
}

$pageTitle = 'Employer Details';
$conn = $GLOBALS['conn'];

$userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
if ($userId <= 0) {
    $_SESSION['error'] = 'Invalid employer reference.';
    redirect('employers.php');
}

$employerStmt = $conn->prepare(
    'SELECT u.user_id, u.full_name, u.email, u.phone, u.status, u.created_at,
        e.employer_id, e.job_title, e.company_id,
        c.company_name, c.industry, c.description, c.verification_status, c.logo, c.location, c.website
     FROM users u
     INNER JOIN employers e ON u.user_id = e.user_id
     LEFT JOIN companies c ON e.company_id = c.company_id
     WHERE u.user_id = ? AND u.role = ?
     LIMIT 1'
);
$employerRole = 'employer';
$employerStmt->bind_param('is', $userId, $employerRole);
$employerStmt->execute();
$employerResult = $employerStmt->get_result();
$employer = $employerResult->fetch_assoc();
$employerStmt->close();

if (!$employer) {
    $_SESSION['error'] = 'Employer account not found.';
    redirect('employers.php');
}

$companyId = $employer['company_id'] ?? null;

$jobsStmt = $conn->prepare(
    'SELECT job_id, title, status, created_at
     FROM jobs
     WHERE company_id = ?
     ORDER BY created_at DESC
     LIMIT 5'
);
$jobsStmt->bind_param('i', $companyId);
$jobsStmt->execute();
$jobsResult = $jobsStmt->get_result();
$recentJobs = $jobsResult->fetch_all(MYSQLI_ASSOC);
$jobsStmt->close();

$totalJobs = 0;
$openJobs = 0;
$closedJobs = 0;
$totalApplications = 0;

$jobCountStmt = $conn->prepare('SELECT COUNT(*) FROM jobs WHERE company_id = ?');
$jobCountStmt->bind_param('i', $companyId);
$jobCountStmt->execute();
$jobCountStmt->bind_result($totalJobs);
$jobCountStmt->fetch();
$jobCountStmt->close();

$openJobsStmt = $conn->prepare("SELECT COUNT(*) FROM jobs WHERE company_id = ? AND status = 'Open'");
$openJobsStmt->bind_param('i', $companyId);
$openJobsStmt->execute();
$openJobsStmt->bind_result($openJobs);
$openJobsStmt->fetch();
$openJobsStmt->close();

$closedJobsStmt = $conn->prepare("SELECT COUNT(*) FROM jobs WHERE company_id = ? AND status = 'Closed'");
$closedJobsStmt->bind_param('i', $companyId);
$closedJobsStmt->execute();
$closedJobsStmt->bind_result($closedJobs);
$closedJobsStmt->fetch();
$closedJobsStmt->close();

$applicationsStmt = $conn->prepare(
    'SELECT COUNT(*)
     FROM applications a
     INNER JOIN jobs j ON a.job_id = j.job_id
     WHERE j.company_id = ?'
);
$applicationsStmt->bind_param('i', $companyId);
$applicationsStmt->execute();
$applicationsStmt->bind_result($totalApplications);
$applicationsStmt->fetch();
$applicationsStmt->close();

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
                        <h1 class="h4 fw-bold mb-1">Employer Details</h1>
                        <p class="text-muted mb-0">Review employer account details and company activity.</p>
                    </div>
                    <a href="employers.php" class="btn btn-outline-custom">Back to Employers</a>
                </div>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Account Information</h2>
                            <p class="mb-2"><strong>Employer Name:</strong> <?php echo htmlspecialchars($employer['full_name']); ?></p>
                            <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($employer['email']); ?></p>
                            <p class="mb-2"><strong>Phone:</strong> <?php echo htmlspecialchars($employer['phone'] ?: 'N/A'); ?></p>
                            <p class="mb-2"><strong>Status:</strong> <span class="badge bg-success"><?php echo htmlspecialchars(ucfirst($employer['status'] ?? 'inactive')); ?></span></p>
                            <p class="mb-0"><strong>Registration Date:</strong> <?php echo date('d M Y', strtotime($employer['created_at'])); ?></p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Company Information</h2>
                            <?php if (!empty($employer['logo'])): ?>
                                <img src="<?php echo BASE_URL; ?>uploads/company_logos/<?php echo htmlspecialchars($employer['logo']); ?>" alt="Company logo" class="img-fluid rounded-4 mb-3" style="max-height: 120px;">
                            <?php endif; ?>
                            <p class="mb-2"><strong>Company Name:</strong> <?php echo htmlspecialchars($employer['company_name'] ?: 'Not set'); ?></p>
                            <p class="mb-2"><strong>Industry:</strong> <?php echo htmlspecialchars($employer['industry'] ?: 'Not set'); ?></p>
                            <p class="mb-2"><strong>Location:</strong> <?php echo htmlspecialchars($employer['location'] ?: 'Not set'); ?></p>
                            <p class="mb-2"><strong>Website:</strong> <?php echo !empty($employer['website']) ? '<a href="' . htmlspecialchars($employer['website']) . '" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($employer['website']) . '</a>' : 'Not set'; ?></p>
                            <p class="mb-2"><strong>Verification Status:</strong> <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($employer['verification_status'] ?? 'Pending'); ?></span></p>
                            <p class="mb-0"><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($employer['description'] ?: 'No description provided.')); ?></p>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Job Statistics</h2>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 d-flex justify-content-between"><span>Total Jobs Posted</span><strong><?php echo (int) $totalJobs; ?></strong></li>
                                <li class="list-group-item px-0 d-flex justify-content-between"><span>Open Jobs</span><strong><?php echo (int) $openJobs; ?></strong></li>
                                <li class="list-group-item px-0 d-flex justify-content-between"><span>Closed Jobs</span><strong><?php echo (int) $closedJobs; ?></strong></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h2 class="h6 fw-semibold mb-3">Application Statistics</h2>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 d-flex justify-content-between"><span>Total Applications Received</span><strong><?php echo (int) $totalApplications; ?></strong></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mt-4 border rounded-4 p-4">
                    <h2 class="h6 fw-semibold mb-3">Admin Actions</h2>
                    <div class="d-flex flex-wrap gap-2">
                        <?php if (($employer['status'] ?? 'inactive') !== 'active'): ?>
                            <form method="post" action="update_employer_status.php">
                                <input type="hidden" name="user_id" value="<?php echo (int) $employer['user_id']; ?>">
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="btn btn-success">Activate Employer</button>
                            </form>
                        <?php endif; ?>
                        <?php if (($employer['status'] ?? 'inactive') !== 'inactive'): ?>
                            <form method="post" action="update_employer_status.php">
                                <input type="hidden" name="user_id" value="<?php echo (int) $employer['user_id']; ?>">
                                <input type="hidden" name="status" value="inactive">
                                <button type="submit" class="btn btn-secondary">Deactivate Employer</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="update_company_verification.php">
                            <input type="hidden" name="user_id" value="<?php echo (int) $employer['user_id']; ?>">
                            <input type="hidden" name="verification_status" value="approved">
                            <button type="submit" class="btn btn-outline-success">Approve Company</button>
                        </form>
                        <form method="post" action="update_company_verification.php">
                            <input type="hidden" name="user_id" value="<?php echo (int) $employer['user_id']; ?>">
                            <input type="hidden" name="verification_status" value="rejected">
                            <button type="submit" class="btn btn-outline-danger">Reject Company</button>
                        </form>
                    </div>
                </div>

                <div class="mt-4 border rounded-4 p-4">
                    <h2 class="h6 fw-semibold mb-3">Recent Jobs Posted</h2>
                    <?php if (empty($recentJobs)): ?>
                        <p class="text-muted mb-0">No jobs posted yet.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentJobs as $job): ?>
                                <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="mb-1 fw-semibold"><?php echo htmlspecialchars($job['title']); ?></p>
                                        <p class="small text-muted mb-0">Status: <?php echo htmlspecialchars($job['status']); ?></p>
                                    </div>
                                    <span class="small text-muted"><?php echo date('d M Y', strtotime($job['created_at'])); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
