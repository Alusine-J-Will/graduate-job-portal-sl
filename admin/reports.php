<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please sign in with an administrator account.';
    redirect(BASE_URL . 'auth/login.php');
}

$pageTitle = 'Admin Reports & Analytics';
$conn = $GLOBALS['conn'];

$stats = [
    'total_users' => 0,
    'graduates' => 0,
    'employers' => 0,
    'admins' => 0,
    'active_accounts' => 0,
    'inactive_accounts' => 0,
    'total_jobs' => 0,
    'open_jobs' => 0,
    'closed_jobs' => 0,
    'draft_jobs' => 0,
    'jobs_this_month' => 0,
    'total_applications' => 0,
    'pending_applications' => 0,
    'reviewed_applications' => 0,
    'shortlisted_applications' => 0,
    'accepted_applications' => 0,
    'rejected_applications' => 0,
    'total_companies' => 0,
    'approved_companies' => 0,
    'pending_companies' => 0,
    'rejected_companies' => 0,
];

function fetchCount(mysqli $conn, string $sql): int
{
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($value);
    $stmt->fetch();
    $stmt->close();
    return (int) $value;
}

$stats['total_users'] = fetchCount($conn, 'SELECT COUNT(*) FROM users');
$stats['graduates'] = fetchCount($conn, "SELECT COUNT(*) FROM users WHERE role = 'graduate'");
$stats['employers'] = fetchCount($conn, "SELECT COUNT(*) FROM users WHERE role = 'employer'");
$stats['admins'] = fetchCount($conn, "SELECT COUNT(*) FROM users WHERE role = 'admin'");
$stats['active_accounts'] = fetchCount($conn, "SELECT COUNT(*) FROM users WHERE status = 'active'");
$stats['inactive_accounts'] = fetchCount($conn, "SELECT COUNT(*) FROM users WHERE status = 'inactive'");

$stats['total_jobs'] = fetchCount($conn, 'SELECT COUNT(*) FROM jobs');
$stats['open_jobs'] = fetchCount($conn, "SELECT COUNT(*) FROM jobs WHERE status = 'Open'");
$stats['closed_jobs'] = fetchCount($conn, "SELECT COUNT(*) FROM jobs WHERE status = 'Closed'");
$stats['draft_jobs'] = fetchCount($conn, "SELECT COUNT(*) FROM jobs WHERE status = 'Draft'");
$stats['jobs_this_month'] = fetchCount($conn, "SELECT COUNT(*) FROM jobs WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())");

$stats['total_applications'] = fetchCount($conn, 'SELECT COUNT(*) FROM applications');
$stats['pending_applications'] = fetchCount($conn, "SELECT COUNT(*) FROM applications WHERE status = 'Submitted'");
$stats['reviewed_applications'] = fetchCount($conn, "SELECT COUNT(*) FROM applications WHERE status IN ('Under Review', 'Interview')");
$stats['shortlisted_applications'] = fetchCount($conn, "SELECT COUNT(*) FROM applications WHERE status = 'Shortlisted'");
$stats['accepted_applications'] = fetchCount($conn, "SELECT COUNT(*) FROM applications WHERE status = 'Accepted'");
$stats['rejected_applications'] = fetchCount($conn, "SELECT COUNT(*) FROM applications WHERE status = 'Rejected'");

$stats['total_companies'] = fetchCount($conn, 'SELECT COUNT(*) FROM companies');
$stats['approved_companies'] = fetchCount($conn, "SELECT COUNT(*) FROM companies WHERE verification_status = 'Approved'");
$stats['pending_companies'] = fetchCount($conn, "SELECT COUNT(*) FROM companies WHERE verification_status = 'Pending'");
$stats['rejected_companies'] = fetchCount($conn, "SELECT COUNT(*) FROM companies WHERE verification_status = 'Rejected'");

$topEmployers = [];
$topEmployerStmt = $conn->prepare(
    'SELECT c.company_name, COUNT(j.job_id) AS total_jobs
     FROM companies c
     LEFT JOIN jobs j ON c.company_id = j.company_id
     GROUP BY c.company_id
     ORDER BY total_jobs DESC, c.company_name ASC
     LIMIT 10'
);
$topEmployerStmt->execute();
$topEmployerResult = $topEmployerStmt->get_result();
$topEmployers = $topEmployerResult->fetch_all(MYSQLI_ASSOC);
$topEmployerStmt->close();

$topCategories = [];
$topCategoriesStmt = $conn->prepare(
    'SELECT jc.category_name, COUNT(j.job_id) AS total_jobs
     FROM job_categories jc
     LEFT JOIN jobs j ON jc.category_id = j.category_id
     GROUP BY jc.category_id
     ORDER BY total_jobs DESC, jc.category_name ASC
     LIMIT 10'
);
$topCategoriesStmt->execute();
$topCategoriesResult = $topCategoriesStmt->get_result();
$topCategories = $topCategoriesResult->fetch_all(MYSQLI_ASSOC);
$topCategoriesStmt->close();

$latestRegistrations = [];
$latestRegistrationsStmt = $conn->prepare(
    'SELECT user_id, full_name, email, role, status, created_at
     FROM users
     ORDER BY created_at DESC
     LIMIT 10'
);
$latestRegistrationsStmt->execute();
$latestRegistrationsResult = $latestRegistrationsStmt->get_result();
$latestRegistrations = $latestRegistrationsResult->fetch_all(MYSQLI_ASSOC);
$latestRegistrationsStmt->close();

$latestJobs = [];
$latestJobsStmt = $conn->prepare(
    'SELECT j.job_id, j.title, c.company_name, j.status, j.created_at
     FROM jobs j
     LEFT JOIN companies c ON j.company_id = c.company_id
     ORDER BY j.created_at DESC
     LIMIT 10'
);
$latestJobsStmt->execute();
$latestJobsResult = $latestJobsStmt->get_result();
$latestJobs = $latestJobsResult->fetch_all(MYSQLI_ASSOC);
$latestJobsStmt->close();

$latestApplications = [];
$latestApplicationsStmt = $conn->prepare(
    'SELECT a.application_id, u.full_name AS applicant_name, j.title AS job_title, a.status, a.application_date
     FROM applications a
     INNER JOIN graduates g ON a.graduate_id = g.graduate_id
     INNER JOIN users u ON g.user_id = u.user_id
     INNER JOIN jobs j ON a.job_id = j.job_id
     ORDER BY a.application_date DESC
     LIMIT 10'
);
$latestApplicationsStmt->execute();
$latestApplicationsResult = $latestApplicationsStmt->get_result();
$latestApplications = $latestApplicationsResult->fetch_all(MYSQLI_ASSOC);
$latestApplicationsStmt->close();

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
                    <h1 class="h3 fw-bold mb-1">Reports & Analytics</h1>
                    <p class="text-muted mb-0">Real-time system statistics and insights for administrators.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="export_users.php" class="btn btn-outline-custom">Export Users Report</a>
                    <a href="export_jobs.php" class="btn btn-outline-custom">Export Jobs Report</a>
                    <a href="export_applications.php" class="btn btn-outline-custom">Export Applications Report</a>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Total Users</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['total_users']; ?></h2>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Total Graduates</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['graduates']; ?></h2>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Total Employers</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['employers']; ?></h2>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Total Admins</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['admins']; ?></h2>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Active Accounts</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['active_accounts']; ?></h2>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Inactive Accounts</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['inactive_accounts']; ?></h2>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Total Jobs</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['total_jobs']; ?></h2>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Open Jobs</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['open_jobs']; ?></h2>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Closed Jobs</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['closed_jobs']; ?></h2>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Draft Jobs</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['draft_jobs']; ?></h2>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Jobs Posted This Month</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['jobs_this_month']; ?></h2>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Total Applications</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['total_applications']; ?></h2>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Pending Applications</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['pending_applications']; ?></h2>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Reviewed Applications</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['reviewed_applications']; ?></h2>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Shortlisted Applications</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['shortlisted_applications']; ?></h2>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Accepted Applications</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['accepted_applications']; ?></h2>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Rejected Applications</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['rejected_applications']; ?></h2>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Total Companies</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['total_companies']; ?></h2>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Approved Companies</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['approved_companies']; ?></h2>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Pending Companies</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['pending_companies']; ?></h2>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <p class="text-uppercase small text-muted mb-2">Rejected Companies</p>
                        <h2 class="h4 mb-0"><?php echo (int) $stats['rejected_companies']; ?></h2>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card-ui p-4 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h5 fw-semibold mb-0">Top Employers</h2>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Company Name</th>
                                        <th class="text-end">Jobs Posted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($topEmployers)): ?>
                                        <tr><td colspan="2" class="text-center text-muted">No employer data available.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($topEmployers as $employer): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($employer['company_name'] ?: 'Unknown Company'); ?></td>
                                                <td class="text-end"><?php echo (int) $employer['total_jobs']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card-ui p-4 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h5 fw-semibold mb-0">Top Job Categories</h2>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Category Name</th>
                                        <th class="text-end">Jobs</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($topCategories)): ?>
                                        <tr><td colspan="2" class="text-center text-muted">No category data available.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($topCategories as $category): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                                <td class="text-end"><?php echo (int) $category['total_jobs']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <h2 class="h5 fw-semibold mb-3">Latest Registrations</h2>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($latestRegistrations)): ?>
                                        <tr><td colspan="3" class="text-center text-muted">No registrations yet.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($latestRegistrations as $user): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars(ucfirst($user['role'])); ?></td>
                                                <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <h2 class="h5 fw-semibold mb-3">Latest Jobs Posted</h2>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Job</th>
                                        <th>Company</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($latestJobs)): ?>
                                        <tr><td colspan="2" class="text-center text-muted">No recent job postings.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($latestJobs as $job): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($job['title']); ?></td>
                                                <td><?php echo htmlspecialchars($job['company_name'] ?: 'Unknown'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <h2 class="h5 fw-semibold mb-3">Latest Applications</h2>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Applicant</th>
                                        <th>Job</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($latestApplications)): ?>
                                        <tr><td colspan="2" class="text-center text-muted">No recent applications.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($latestApplications as $application): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($application['applicant_name']); ?></td>
                                                <td><?php echo htmlspecialchars($application['job_title']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
