<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please sign in with an administrator account.';
    redirect(BASE_URL . 'auth/login.php');
}

$pageTitle = 'Manage Applications';
$conn = $GLOBALS['conn'];

$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$companyFilter = trim($_GET['company'] ?? '');
$dateFilter = trim($_GET['date_filter'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$validStatuses = ['Pending', 'Reviewed', 'Shortlisted', 'Rejected', 'Accepted'];
$validDateFilters = ['today', 'last_7_days', 'last_30_days', 'this_month', 'this_year'];

$whereClauses = ['1=1'];
$params = [];
$types = '';

if ($search !== '') {
    $searchTerm = '%' . $search . '%';
    $whereClauses[] = '(u.full_name LIKE ? OR u.email LIKE ? OR j.title LIKE ? OR c.company_name LIKE ?)';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ssss';
}

if (in_array($statusFilter, $validStatuses, true)) {
    $whereClauses[] = 'a.status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}

if (ctype_digit($companyFilter)) {
    $whereClauses[] = 'j.company_id = ?';
    $params[] = (int) $companyFilter;
    $types .= 'i';
}

if (in_array($dateFilter, $validDateFilters, true)) {
    switch ($dateFilter) {
        case 'today':
            $whereClauses[] = 'a.application_date >= CURDATE()';
            break;
        case 'last_7_days':
            $whereClauses[] = 'a.application_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
            break;
        case 'last_30_days':
            $whereClauses[] = 'a.application_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
            break;
        case 'this_month':
            $whereClauses[] = 'a.application_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)';
            break;
        case 'this_year':
            $whereClauses[] = 'a.application_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)';
            break;
    }
}

$whereSql = implode(' AND ', $whereClauses);

$countSql = 'SELECT COUNT(*)
    FROM applications a
    INNER JOIN graduates g ON a.graduate_id = g.graduate_id
    INNER JOIN users u ON g.user_id = u.user_id
    INNER JOIN jobs j ON a.job_id = j.job_id
    LEFT JOIN companies c ON j.company_id = c.company_id
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
$countStmt->bind_result($totalApplications);
$countStmt->fetch();
$countStmt->close();

$listSql = 'SELECT a.application_id, u.full_name AS graduate_name, u.email, j.title AS job_title, c.company_name, a.status, a.application_date
    FROM applications a
    INNER JOIN graduates g ON a.graduate_id = g.graduate_id
    INNER JOIN users u ON g.user_id = u.user_id
    INNER JOIN jobs j ON a.job_id = j.job_id
    LEFT JOIN companies c ON j.company_id = c.company_id
    WHERE ' . $whereSql . '
    ORDER BY a.application_date DESC
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
$applicationsResult = $listStmt->get_result();
$applications = $applicationsResult->fetch_all(MYSQLI_ASSOC);
$listStmt->close();

$totalPages = max(1, (int) ceil($totalApplications / $perPage));

$companiesStmt = $conn->prepare('SELECT company_id, company_name FROM companies ORDER BY company_name ASC');
$companiesStmt->execute();
$companiesResult = $companiesStmt->get_result();
$companies = $companiesResult->fetch_all(MYSQLI_ASSOC);
$companiesStmt->close();

function getApplicationStatusBadge(string $status): string
{
    $badges = [
        'Pending' => 'bg-warning text-dark',
        'Reviewed' => 'bg-info text-dark',
        'Shortlisted' => 'bg-primary',
        'Rejected' => 'bg-danger',
        'Accepted' => 'bg-success',
    ];

    return $badges[$status] ?? 'bg-secondary';
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
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                    <div>
                        <h1 class="h4 fw-bold mb-1">Manage Applications</h1>
                        <p class="text-muted mb-0">Review job applications submitted by graduates.</p>
                    </div>
                    <a href="dashboard.php" class="btn btn-outline-custom">Back to Dashboard</a>
                </div>

                <form method="get" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Graduate name, email, job title, company">
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
                        <label class="form-label fw-semibold">Company</label>
                        <select class="form-select" name="company">
                            <option value="">All</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?php echo (int) $company['company_id']; ?>"<?php echo $companyFilter === (string) $company['company_id'] ? ' selected' : ''; ?>><?php echo htmlspecialchars($company['company_name']); ?></option>
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
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary-custom w-100">Apply Filters</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Application ID</th>
                                <th>Graduate Name</th>
                                <th>Job Title</th>
                                <th>Company Name</th>
                                <th>Status</th>
                                <th>Application Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($applications)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No applications found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($applications as $application): ?>
                                    <tr>
                                        <td>#<?php echo (int) $application['application_id']; ?></td>
                                        <td><?php echo htmlspecialchars($application['graduate_name']); ?></td>
                                        <td><?php echo htmlspecialchars($application['job_title']); ?></td>
                                        <td><?php echo htmlspecialchars($application['company_name'] ?: 'N/A'); ?></td>
                                        <td><span class="badge <?php echo getApplicationStatusBadge($application['status'] ?? 'Pending'); ?>"><?php echo htmlspecialchars($application['status'] ?? 'Pending'); ?></span></td>
                                        <td><?php echo date('d M Y', strtotime($application['application_date'])); ?></td>
                                        <td>
                                            <a href="application_details.php?application_id=<?php echo (int) $application['application_id']; ?>" class="btn btn-sm btn-outline-custom">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav class="mt-4" aria-label="Application pagination">
                        <ul class="pagination mb-0">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="applications.php?search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>&company=<?php echo urlencode($companyFilter); ?>&date_filter=<?php echo urlencode($dateFilter); ?>&page=<?php echo max(1, $page - 1); ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="applications.php?search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>&company=<?php echo urlencode($companyFilter); ?>&date_filter=<?php echo urlencode($dateFilter); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="applications.php?search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>&company=<?php echo urlencode($companyFilter); ?>&date_filter=<?php echo urlencode($dateFilter); ?>&page=<?php echo min($totalPages, $page + 1); ?>">Next</a>
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
