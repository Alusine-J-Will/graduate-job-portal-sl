<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please sign in with an administrator account.';
    redirect(BASE_URL . 'auth/login.php');
}

$pageTitle = 'Admin Dashboard';
$conn = $GLOBALS['conn'];

$stats = [
    'graduates' => 0,
    'employers' => 0,
    'jobs' => 0,
    'applications' => 0,
    'active_users' => 0,
    'pending_companies' => 0,
    'open_jobs' => 0,
    'closed_jobs' => 0,
];

$graduateCountStmt = $conn->prepare('SELECT COUNT(*) FROM graduates');
$graduateCountStmt->execute();
$graduateCountStmt->bind_result($stats['graduates']);
$graduateCountStmt->fetch();
$graduateCountStmt->close();

$employerCountStmt = $conn->prepare('SELECT COUNT(*) FROM employers');
$employerCountStmt->execute();
$employerCountStmt->bind_result($stats['employers']);
$employerCountStmt->fetch();
$employerCountStmt->close();

$jobCountStmt = $conn->prepare('SELECT COUNT(*) FROM jobs');
$jobCountStmt->execute();
$jobCountStmt->bind_result($stats['jobs']);
$jobCountStmt->fetch();
$jobCountStmt->close();

$applicationCountStmt = $conn->prepare('SELECT COUNT(*) FROM applications');
$applicationCountStmt->execute();
$applicationCountStmt->bind_result($stats['applications']);
$applicationCountStmt->fetch();
$applicationCountStmt->close();

$activeUsersStmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE status = 'active'");
$activeUsersStmt->execute();
$activeUsersStmt->bind_result($stats['active_users']);
$activeUsersStmt->fetch();
$activeUsersStmt->close();

$pendingCompaniesStmt = $conn->prepare("SELECT COUNT(*) FROM companies WHERE verification_status = 'Pending'");
$pendingCompaniesStmt->execute();
$pendingCompaniesStmt->bind_result($stats['pending_companies']);
$pendingCompaniesStmt->fetch();
$pendingCompaniesStmt->close();

$openJobsStmt = $conn->prepare("SELECT COUNT(*) FROM jobs WHERE status = 'Open'");
$openJobsStmt->execute();
$openJobsStmt->bind_result($stats['open_jobs']);
$openJobsStmt->fetch();
$openJobsStmt->close();

$closedJobsStmt = $conn->prepare("SELECT COUNT(*) FROM jobs WHERE status = 'Closed'");
$closedJobsStmt->execute();
$closedJobsStmt->bind_result($stats['closed_jobs']);
$closedJobsStmt->fetch();
$closedJobsStmt->close();

$recentGraduates = [];
$recentGraduatesStmt = $conn->prepare(
    'SELECT u.user_id, u.full_name, u.email, u.created_at
     FROM users u
     INNER JOIN graduates g ON u.user_id = g.user_id
     WHERE u.role = ?
     ORDER BY u.created_at DESC
     LIMIT 5'
);
$graduateRole = 'graduate';
$recentGraduatesStmt->bind_param('s', $graduateRole);
$recentGraduatesStmt->execute();
$recentGraduatesResult = $recentGraduatesStmt->get_result();
$recentGraduates = $recentGraduatesResult->fetch_all(MYSQLI_ASSOC);
$recentGraduatesStmt->close();

$recentEmployers = [];
$recentEmployersStmt = $conn->prepare(
    'SELECT u.user_id, u.full_name, u.email, u.created_at
     FROM users u
     INNER JOIN employers e ON u.user_id = e.user_id
     WHERE u.role = ?
     ORDER BY u.created_at DESC
     LIMIT 5'
);
$employerRole = 'employer';
$recentEmployersStmt->bind_param('s', $employerRole);
$recentEmployersStmt->execute();
$recentEmployersResult = $recentEmployersStmt->get_result();
$recentEmployers = $recentEmployersResult->fetch_all(MYSQLI_ASSOC);
$recentEmployersStmt->close();

$recentJobs = [];
$recentJobsStmt = $conn->prepare(
    'SELECT j.job_id, j.title, c.company_name, j.created_at
     FROM jobs j
     LEFT JOIN companies c ON j.company_id = c.company_id
     ORDER BY j.created_at DESC
     LIMIT 5'
);
$recentJobsStmt->execute();
$recentJobsResult = $recentJobsStmt->get_result();
$recentJobs = $recentJobsResult->fetch_all(MYSQLI_ASSOC);
$recentJobsStmt->close();

$recentApplications = [];
$recentApplicationsStmt = $conn->prepare(
    'SELECT a.application_id, j.title AS job_title, u.full_name AS applicant_name, a.application_date
     FROM applications a
     INNER JOIN jobs j ON a.job_id = j.job_id
     INNER JOIN graduates g ON a.graduate_id = g.graduate_id
     INNER JOIN users u ON g.user_id = u.user_id
     ORDER BY a.application_date DESC
     LIMIT 5'
);
$recentApplicationsStmt->execute();
$recentApplicationsResult = $recentApplicationsStmt->get_result();
$recentApplications = $recentApplicationsResult->fetch_all(MYSQLI_ASSOC);
$recentApplicationsStmt->close();

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

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1">Admin Dashboard</h1>
                    <p class="text-muted mb-0">Overview of graduates, employers, jobs, and applications in GradConnect SL.</p>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card-ui p-4 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-uppercase small text-muted mb-2">Total Graduates</p>
                                <h2 class="h4 mb-0"><?php echo (int) $stats['graduates']; ?></h2>
                            </div>
                            <div class="rounded-4 bg-primary bg-opacity-10 text-primary p-3"><i class="fas fa-user-graduate"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card-ui p-4 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-uppercase small text-muted mb-2">Total Employers</p>
                                <h2 class="h4 mb-0"><?php echo (int) $stats['employers']; ?></h2>
                            </div>
                            <div class="rounded-4 bg-success bg-opacity-10 text-success p-3"><i class="fas fa-building"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card-ui p-4 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-uppercase small text-muted mb-2">Total Jobs</p>
                                <h2 class="h4 mb-0"><?php echo (int) $stats['jobs']; ?></h2>
                            </div>
                            <div class="rounded-4 bg-warning bg-opacity-10 text-warning p-3"><i class="fas fa-briefcase"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card-ui p-4 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-uppercase small text-muted mb-2">Total Applications</p>
                                <h2 class="h4 mb-0"><?php echo (int) $stats['applications']; ?></h2>
                            </div>
                            <div class="rounded-4 bg-info bg-opacity-10 text-info p-3"><i class="fas fa-file-alt"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-ui p-4 bg-white mb-4">
                <h2 class="h5 fw-semibold mb-3">Quick Actions</h2>
                <div class="d-flex flex-wrap gap-2">
                    <a href="graduates.php" class="btn btn-outline-custom">Manage Graduates</a>
                    <a href="employers.php" class="btn btn-outline-custom">Manage Employers</a>
                    <a href="jobs.php" class="btn btn-outline-custom">Manage Jobs</a>
                    <a href="applications.php" class="btn btn-outline-custom">Manage Applications</a>
                    <a href="reports.php" class="btn btn-outline-custom">Reports</a>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card-ui p-4 bg-white h-100">
                        <h2 class="h5 fw-semibold mb-3">Recent Activity</h2>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h3 class="h6 fw-semibold mb-3">Latest Graduates</h3>
                                <?php if (empty($recentGraduates)): ?>
                                    <p class="text-muted mb-0">No graduates found.</p>
                                <?php else: ?>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($recentGraduates as $graduate): ?>
                                            <li class="list-group-item px-0 py-2">
                                                <div class="d-flex justify-content-between align-items-start gap-3">
                                                    <div>
                                                        <p class="mb-1 fw-semibold"><?php echo htmlspecialchars($graduate['full_name']); ?></p>
                                                        <p class="small text-muted mb-0"><?php echo htmlspecialchars($graduate['email']); ?></p>
                                                    </div>
                                                    <span class="small text-muted"><?php echo date('d M Y', strtotime($graduate['created_at'])); ?></span>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h3 class="h6 fw-semibold mb-3">Latest Employers</h3>
                                <?php if (empty($recentEmployers)): ?>
                                    <p class="text-muted mb-0">No employers found.</p>
                                <?php else: ?>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($recentEmployers as $employer): ?>
                                            <li class="list-group-item px-0 py-2">
                                                <div class="d-flex justify-content-between align-items-start gap-3">
                                                    <div>
                                                        <p class="mb-1 fw-semibold"><?php echo htmlspecialchars($employer['full_name']); ?></p>
                                                        <p class="small text-muted mb-0"><?php echo htmlspecialchars($employer['email']); ?></p>
                                                    </div>
                                                    <span class="small text-muted"><?php echo date('d M Y', strtotime($employer['created_at'])); ?></span>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <h2 class="h5 fw-semibold mb-3">System Overview</h2>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center"><span>Active Users</span><strong><?php echo (int) $stats['active_users']; ?></strong></li>
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center"><span>Pending Companies</span><strong><?php echo (int) $stats['pending_companies']; ?></strong></li>
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center"><span>Open Jobs</span><strong><?php echo (int) $stats['open_jobs']; ?></strong></li>
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center"><span>Closed Jobs</span><strong><?php echo (int) $stats['closed_jobs']; ?></strong></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card-ui p-4 bg-white h-100">
                        <h2 class="h5 fw-semibold mb-3">Latest Posted Jobs</h2>
                        <?php if (empty($recentJobs)): ?>
                            <p class="text-muted mb-0">No jobs found.</p>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($recentJobs as $job): ?>
                                    <li class="list-group-item px-0 py-2">
                                        <div class="d-flex justify-content-between align-items-start gap-3">
                                            <div>
                                                <p class="mb-1 fw-semibold"><?php echo htmlspecialchars($job['title']); ?></p>
                                                <p class="small text-muted mb-0"><?php echo htmlspecialchars($job['company_name'] ?: 'Company not listed'); ?></p>
                                            </div>
                                            <span class="small text-muted"><?php echo date('d M Y', strtotime($job['created_at'])); ?></span>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card-ui p-4 bg-white h-100">
                        <h2 class="h5 fw-semibold mb-3">Latest Applications</h2>
                        <?php if (empty($recentApplications)): ?>
                            <p class="text-muted mb-0">No applications found.</p>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($recentApplications as $application): ?>
                                    <li class="list-group-item px-0 py-2">
                                        <div class="d-flex justify-content-between align-items-start gap-3">
                                            <div>
                                                <p class="mb-1 fw-semibold"><?php echo htmlspecialchars($application['job_title']); ?></p>
                                                <p class="small text-muted mb-0"><?php echo htmlspecialchars($application['applicant_name']); ?></p>
                                            </div>
                                            <span class="small text-muted"><?php echo date('d M Y', strtotime($application['application_date'])); ?></span>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
