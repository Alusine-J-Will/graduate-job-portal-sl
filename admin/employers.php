<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please sign in with an administrator account.';
    redirect(BASE_URL . 'auth/login.php');
}

$pageTitle = 'Manage Employers';
$conn = $GLOBALS['conn'];

$search = trim($_GET['search'] ?? '');
$verificationFilter = trim($_GET['verification_status'] ?? '');
$accountFilter = trim($_GET['account_status'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$whereClauses = ['u.role = ?'];
$params = ['employer'];
$types = 's';

if ($search !== '') {
    $whereClauses[] = '(c.company_name LIKE ? OR u.full_name LIKE ? OR u.email LIKE ? OR c.industry LIKE ? OR e.job_title LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $types .= 'sssss';
}

if ($verificationFilter !== '' && in_array($verificationFilter, ['Approved', 'Pending', 'Rejected'], true)) {
    $whereClauses[] = 'c.verification_status = ?';
    $params[] = $verificationFilter;
    $types .= 's';
}

if ($accountFilter !== '' && in_array($accountFilter, ['active', 'inactive'], true)) {
    $whereClauses[] = 'u.status = ?';
    $params[] = $accountFilter;
    $types .= 's';
}

$whereSql = implode(' AND ', $whereClauses);

$countSql = 'SELECT COUNT(*)
    FROM users u
    INNER JOIN employers e ON u.user_id = e.user_id
    LEFT JOIN companies c ON e.company_id = c.company_id
    WHERE ' . $whereSql;

$countStmt = $conn->prepare($countSql);
$refs = [];
foreach ($params as $key => $value) {
    $refs[$key] = &$params[$key];
}
array_unshift($refs, $types);
call_user_func_array([$countStmt, 'bind_param'], $refs);
$countStmt->execute();
$countStmt->bind_result($totalEmployers);
$countStmt->fetch();
$countStmt->close();

$listSql = 'SELECT u.user_id, u.full_name, u.email, u.phone, u.status, u.created_at,
    e.employer_id, e.job_title, c.company_id, c.company_name, c.industry, c.verification_status,
    (SELECT COUNT(*) FROM jobs j WHERE j.company_id = c.company_id) AS total_jobs_posted
    FROM users u
    INNER JOIN employers e ON u.user_id = e.user_id
    LEFT JOIN companies c ON e.company_id = c.company_id
    WHERE ' . $whereSql . '
    ORDER BY u.created_at DESC
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
$employersResult = $listStmt->get_result();
$employers = $employersResult->fetch_all(MYSQLI_ASSOC);
$listStmt->close();

$totalPages = max(1, (int) ceil($totalEmployers / $perPage));

function getVerificationBadge(string $status): string
{
    $badges = [
        'Approved' => 'bg-success',
        'Pending' => 'bg-warning text-dark',
        'Rejected' => 'bg-danger',
    ];

    return $badges[$status] ?? 'bg-secondary';
}

function getStatusBadge(string $status): string
{
    $badges = [
        'active' => 'bg-success',
        'inactive' => 'bg-secondary',
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
                        <h1 class="h4 fw-bold mb-1">Manage Employers</h1>
                        <p class="text-muted mb-0">Review employer accounts, company verification, and job posting activity.</p>
                    </div>
                    <a href="dashboard.php" class="btn btn-outline-custom">Back to Dashboard</a>
                </div>

                <form method="get" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Company, employer, email, industry, role">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Verification</label>
                        <select class="form-select" name="verification_status">
                            <option value="">All</option>
                            <option value="Approved"<?php echo $verificationFilter === 'Approved' ? ' selected' : ''; ?>>Approved</option>
                            <option value="Pending"<?php echo $verificationFilter === 'Pending' ? ' selected' : ''; ?>>Pending</option>
                            <option value="Rejected"<?php echo $verificationFilter === 'Rejected' ? ' selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Account Status</label>
                        <select class="form-select" name="account_status">
                            <option value="">All</option>
                            <option value="active"<?php echo $accountFilter === 'active' ? ' selected' : ''; ?>>Active</option>
                            <option value="inactive"<?php echo $accountFilter === 'inactive' ? ' selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary-custom w-100">Filter</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Employer ID</th>
                                <th>Company Name</th>
                                <th>Contact Person</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Verification</th>
                                <th>Account Status</th>
                                <th>Jobs Posted</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($employers)): ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">No employers found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($employers as $employer): ?>
                                    <tr>
                                        <td>#<?php echo (int) $employer['employer_id']; ?></td>
                                        <td><?php echo htmlspecialchars($employer['company_name'] ?: 'Company not set'); ?></td>
                                        <td><?php echo htmlspecialchars($employer['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($employer['email']); ?></td>
                                        <td><?php echo htmlspecialchars($employer['phone'] ?: 'N/A'); ?></td>
                                        <td><span class="badge <?php echo getVerificationBadge($employer['verification_status'] ?? 'Pending'); ?>"><?php echo htmlspecialchars($employer['verification_status'] ?? 'Pending'); ?></span></td>
                                        <td><span class="badge <?php echo getStatusBadge($employer['status'] ?? 'inactive'); ?>"><?php echo htmlspecialchars(ucfirst($employer['status'] ?? 'inactive')); ?></span></td>
                                        <td><?php echo (int) $employer['total_jobs_posted']; ?></td>
                                        <td><?php echo date('d M Y', strtotime($employer['created_at'])); ?></td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="employer_details.php?user_id=<?php echo (int) $employer['user_id']; ?>" class="btn btn-sm btn-outline-custom">View</a>
                                                <?php if (($employer['status'] ?? 'inactive') !== 'active'): ?>
                                                    <form method="post" action="update_employer_status.php" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?php echo (int) $employer['user_id']; ?>">
                                                        <input type="hidden" name="status" value="active">
                                                        <button type="submit" class="btn btn-sm btn-success">Activate</button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if (($employer['status'] ?? 'inactive') !== 'inactive'): ?>
                                                    <form method="post" action="update_employer_status.php" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?php echo (int) $employer['user_id']; ?>">
                                                        <input type="hidden" name="status" value="inactive">
                                                        <button type="submit" class="btn btn-sm btn-secondary">Deactivate</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav class="mt-4" aria-label="Employer pagination">
                        <ul class="pagination mb-0">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="employers.php?search=<?php echo urlencode($search); ?>&verification_status=<?php echo urlencode($verificationFilter); ?>&account_status=<?php echo urlencode($accountFilter); ?>&page=<?php echo max(1, $page - 1); ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="employers.php?search=<?php echo urlencode($search); ?>&verification_status=<?php echo urlencode($verificationFilter); ?>&account_status=<?php echo urlencode($accountFilter); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="employers.php?search=<?php echo urlencode($search); ?>&verification_status=<?php echo urlencode($verificationFilter); ?>&account_status=<?php echo urlencode($accountFilter); ?>&page=<?php echo min($totalPages, $page + 1); ?>">Next</a>
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
