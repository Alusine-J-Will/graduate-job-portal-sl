<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please sign in with an administrator account.';
    redirect(BASE_URL . 'auth/login.php');
}

$pageTitle = 'Manage Jobs';
$conn = $GLOBALS['conn'];

$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$employmentTypeFilter = trim($_GET['employment_type'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');
$dateFilter = trim($_GET['date_filter'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$validStatuses = ['Open', 'Closed', 'Draft'];
$validEmploymentTypes = ['Full Time', 'Part Time', 'Contract', 'Internship', 'Temporary'];
$validDateFilters = ['today', 'last_7_days', 'last_30_days', 'this_month', 'this_year'];

$whereClauses = ['1=1'];
$params = [];
$types = '';

if ($search !== '') {
    $searchTerm = '%' . $search . '%';
    $whereClauses[] = '(j.title LIKE ? OR c.company_name LIKE ? OR jc.category_name LIKE ? OR j.location LIKE ? OR j.employment_type LIKE ?)';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sssss';
}

if (in_array($statusFilter, $validStatuses, true)) {
    $whereClauses[] = 'j.status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}

if (in_array($employmentTypeFilter, $validEmploymentTypes, true)) {
    $whereClauses[] = 'j.employment_type = ?';
    $params[] = $employmentTypeFilter;
    $types .= 's';
}

if (ctype_digit($categoryFilter)) {
    $whereClauses[] = 'j.category_id = ?';
    $params[] = (int) $categoryFilter;
    $types .= 'i';
}

if (in_array($dateFilter, $validDateFilters, true)) {
    switch ($dateFilter) {
        case 'today':
            $whereClauses[] = 'j.created_at >= CURDATE()';
            break;
        case 'last_7_days':
            $whereClauses[] = 'j.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
            break;
        case 'last_30_days':
            $whereClauses[] = 'j.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
            break;
        case 'this_month':
            $whereClauses[] = 'j.created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)';
            break;
        case 'this_year':
            $whereClauses[] = 'j.created_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)';
            break;
    }
}

$whereSql = implode(' AND ', $whereClauses);

$countSql = 'SELECT COUNT(*)
    FROM jobs j
    LEFT JOIN companies c ON j.company_id = c.company_id
    LEFT JOIN job_categories jc ON j.category_id = jc.category_id
    WHERE ' . $whereSql;

$countStmt = $conn->prepare($countSql);
if ($types !== '') {
    $countRefs = [];
    foreach ($params as $key => $value) {
        $countRefs[$key] = &$params[$key];
    }
    array_unshift($countRefs, $types);
    call_user_func_array([$countStmt, 'bind_param'], $countRefs);
}
$countStmt->execute();
$countStmt->bind_result($totalJobs);
$countStmt->fetch();
$countStmt->close();

$listSql = 'SELECT j.job_id, j.title, c.company_name, jc.category_name, j.employment_type, j.location, j.status, j.deadline, j.created_at,
    COUNT(a.application_id) AS application_count
    FROM jobs j
    LEFT JOIN companies c ON j.company_id = c.company_id
    LEFT JOIN job_categories jc ON j.category_id = jc.category_id
    LEFT JOIN applications a ON j.job_id = a.job_id
    WHERE ' . $whereSql . '
    GROUP BY j.job_id, j.title, c.company_name, jc.category_name, j.employment_type, j.location, j.status, j.deadline, j.created_at
    ORDER BY j.created_at DESC
    LIMIT ? OFFSET ?';

$listStmt = $conn->prepare($listSql);
$limit = $perPage;
$offsetValue = $offset;
$bindParams = $params;
$bindParams[] = $limit;
$bindParams[] = $offsetValue;
$bindTypes = $types . 'ii';

$bindRefs = [];
foreach ($bindParams as $key => $value) {
    $bindRefs[$key] = &$bindParams[$key];
}
array_unshift($bindRefs, $bindTypes);
call_user_func_array([$listStmt, 'bind_param'], $bindRefs);
$listStmt->execute();
$jobsResult = $listStmt->get_result();
$jobs = $jobsResult->fetch_all(MYSQLI_ASSOC);
$listStmt->close();

$totalPages = max(1, (int) ceil($totalJobs / $perPage));

$categoriesStmt = $conn->prepare('SELECT category_id, category_name FROM job_categories ORDER BY category_name ASC');
$categoriesStmt->execute();
$categoriesResult = $categoriesStmt->get_result();
$categories = $categoriesResult->fetch_all(MYSQLI_ASSOC);
$categoriesStmt->close();

function getJobStatusBadge(string $status): string
{
    $badges = [
        'Open' => 'bg-success',
        'Closed' => 'bg-secondary',
        'Draft' => 'bg-warning text-dark',
    ];

    return $badges[$status] ?? 'bg-secondary';
}

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
                        <h1 class="h4 fw-bold mb-1">Manage Jobs</h1>
                        <p class="text-muted mb-0">Review job listings, monitor applications, and manage job visibility.</p>
                    </div>
                    <a href="dashboard.php" class="btn btn-outline-custom">Back to Dashboard</a>
                </div>

                <form method="get" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Job title, company, category, location, type">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All</option>
                            <?php foreach ($validStatuses as $status): ?>
                                <option value="<?php echo htmlspecialchars($status); ?>"<?php echo $statusFilter === $status ? ' selected' : ''; ?>><?php echo htmlspecialchars($status); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Employment Type</label>
                        <select class="form-select" name="employment_type">
                            <option value="">All</option>
                            <?php foreach ($validEmploymentTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>"<?php echo $employmentTypeFilter === $type ? ' selected' : ''; ?>><?php echo htmlspecialchars($type); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Category</label>
                        <select class="form-select" name="category">
                            <option value="">All</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo (int) $category['category_id']; ?>"<?php echo $categoryFilter === (string) $category['category_id'] ? ' selected' : ''; ?>><?php echo htmlspecialchars($category['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Date</label>
                        <select class="form-select" name="date_filter">
                            <option value="">All Time</option>
                            <option value="today"<?php echo $dateFilter === 'today' ? ' selected' : ''; ?>>Today</option>
                            <option value="last_7_days"<?php echo $dateFilter === 'last_7_days' ? ' selected' : ''; ?>>Last 7 Days</option>
                            <option value="last_30_days"<?php echo $dateFilter === 'last_30_days' ? ' selected' : ''; ?>>Last 30 Days</option>
                            <option value="this_month"<?php echo $dateFilter === 'this_month' ? ' selected' : ''; ?>>This Month</option>
                            <option value="this_year"<?php echo $dateFilter === 'this_year' ? ' selected' : ''; ?>>This Year</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary-custom">Apply Filters</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Job ID</th>
                                <th>Job Title</th>
                                <th>Company</th>
                                <th>Category</th>
                                <th>Employment Type</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Applications</th>
                                <th>Posted</th>
                                <th>Deadline</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($jobs)): ?>
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">No jobs found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($jobs as $job): ?>
                                    <tr>
                                        <td>#<?php echo (int) $job['job_id']; ?></td>
                                        <td><?php echo htmlspecialchars($job['title']); ?></td>
                                        <td><?php echo htmlspecialchars($job['company_name'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($job['category_name'] ?: 'Uncategorized'); ?></td>
                                        <td><?php echo htmlspecialchars($job['employment_type'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($job['location']); ?></td>
                                        <td><span class="badge <?php echo getJobStatusBadge($job['status']); ?>"><?php echo htmlspecialchars($job['status']); ?></span></td>
                                        <td><?php echo (int) $job['application_count']; ?></td>
                                        <td><?php echo !empty($job['created_at']) ? date('d M Y', strtotime($job['created_at'])) : 'N/A'; ?></td>
                                        <td><?php echo !empty($job['deadline']) ? date('d M Y', strtotime($job['deadline'])) : 'N/A'; ?></td>
                                        <td>
                                            <a href="job_details.php?job_id=<?php echo (int) $job['job_id']; ?>" class="btn btn-sm btn-outline-custom">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav class="mt-4" aria-label="Job pagination">
                        <ul class="pagination mb-0">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="jobs.php?search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>&employment_type=<?php echo urlencode($employmentTypeFilter); ?>&category=<?php echo urlencode($categoryFilter); ?>&date_filter=<?php echo urlencode($dateFilter); ?>&page=<?php echo max(1, $page - 1); ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="jobs.php?search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>&employment_type=<?php echo urlencode($employmentTypeFilter); ?>&category=<?php echo urlencode($categoryFilter); ?>&date_filter=<?php echo urlencode($dateFilter); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="jobs.php?search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>&employment_type=<?php echo urlencode($employmentTypeFilter); ?>&category=<?php echo urlencode($categoryFilter); ?>&date_filter=<?php echo urlencode($dateFilter); ?>&page=<?php echo min($totalPages, $page + 1); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
