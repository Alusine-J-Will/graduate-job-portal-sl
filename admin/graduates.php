<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please sign in with an administrator account.';
    redirect(BASE_URL . 'auth/login.php');
}

$pageTitle = 'Manage Graduates';
$conn = $GLOBALS['conn'];

$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$whereClauses = ['u.role = ?'];
$params = ['graduate'];
$types = 's';

if ($search !== '') {
    $whereClauses[] = '(u.full_name LIKE ? OR u.email LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $types .= 'ss';
}

if ($statusFilter !== '' && in_array($statusFilter, ['active', 'pending', 'inactive'], true)) {
    $whereClauses[] = 'u.status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}

$whereSql = implode(' AND ', $whereClauses);

$countSql = 'SELECT COUNT(*)
    FROM users u
    INNER JOIN graduates g ON u.user_id = g.user_id
    WHERE ' . $whereSql;

$countStmt = $conn->prepare($countSql);
$refs = [];
foreach ($params as $key => $value) {
    $refs[$key] = &$params[$key];
}
array_unshift($refs, $types);
call_user_func_array([$countStmt, 'bind_param'], $refs);
$countStmt->execute();
$countStmt->bind_result($totalGraduates);
$countStmt->fetch();
$countStmt->close();

$listSql = 'SELECT u.user_id, u.full_name, u.email, u.phone, u.status, u.created_at,
    g.graduate_id, g.location, g.bio, g.cv, g.profile_picture,
    (SELECT COUNT(*) FROM education e WHERE e.graduate_id = g.graduate_id) AS education_count,
    (SELECT COUNT(*) FROM experience ex WHERE ex.graduate_id = g.graduate_id) AS experience_count,
    (SELECT COUNT(*) FROM graduate_skills gs INNER JOIN skills s ON gs.skill_id = s.skill_id WHERE gs.graduate_id = g.graduate_id) AS skill_count
    FROM users u
    INNER JOIN graduates g ON u.user_id = g.user_id
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
$graduatesResult = $listStmt->get_result();
$graduates = $graduatesResult->fetch_all(MYSQLI_ASSOC);
$listStmt->close();

$totalPages = max(1, (int) ceil($totalGraduates / $perPage));

function getProfileCompletion(array $graduate): string
{
    $completed = 0;
    $total = 6;

    if (!empty($graduate['location'])) {
        $completed++;
    }
    if (!empty($graduate['bio'])) {
        $completed++;
    }
    if (!empty($graduate['cv'])) {
        $completed++;
    }
    if (!empty($graduate['profile_picture'])) {
        $completed++;
    }
    if ((int) $graduate['education_count'] > 0) {
        $completed++;
    }
    if ((int) $graduate['experience_count'] > 0 || (int) $graduate['skill_count'] > 0) {
        $completed++;
    }

    return round(($completed / $total) * 100) . '%';
}

function getStatusBadge(string $status): string
{
    $badges = [
        'active' => 'bg-success',
        'pending' => 'bg-warning text-dark',
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
            <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>
        <div class="col-lg-9">
            <?php displayFlashMessages(); ?>

            <div class="card-ui p-4 bg-white mb-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                    <div>
                        <h1 class="h4 fw-bold mb-1">Manage Graduates</h1>
                        <p class="text-muted mb-0">Monitor graduate accounts, profile completion, and account status.</p>
                    </div>
                    <a href="dashboard.php" class="btn btn-outline-custom">Back to Dashboard</a>
                </div>

                <form method="get" class="row g-3 mb-4">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name or email">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Statuses</option>
                            <option value="active"<?php echo $statusFilter === 'active' ? ' selected' : ''; ?>>Active</option>
                            <option value="pending"<?php echo $statusFilter === 'pending' ? ' selected' : ''; ?>>Pending</option>
                            <option value="inactive"<?php echo $statusFilter === 'inactive' ? ' selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary-custom w-100">Filter</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Profile Completion</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($graduates)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No graduates found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($graduates as $graduate): ?>
                                    <tr>
                                        <td>#<?php echo (int) $graduate['user_id']; ?></td>
                                        <td><?php echo htmlspecialchars($graduate['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($graduate['email']); ?></td>
                                        <td><?php echo htmlspecialchars($graduate['phone'] ?: 'N/A'); ?></td>
                                        <td><?php echo getProfileCompletion($graduate); ?></td>
                                        <td><span class="badge <?php echo getStatusBadge($graduate['status'] ?? 'pending'); ?>"><?php echo htmlspecialchars(ucfirst($graduate['status'] ?? 'pending')); ?></span></td>
                                        <td><?php echo date('d M Y', strtotime($graduate['created_at'])); ?></td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="graduate_details.php?user_id=<?php echo (int) $graduate['user_id']; ?>" class="btn btn-sm btn-outline-custom">View</a>
                                                <?php if (($graduate['status'] ?? 'pending') !== 'active'): ?>
                                                    <form method="post" action="update_graduate_status.php" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?php echo (int) $graduate['user_id']; ?>">
                                                        <input type="hidden" name="status" value="active">
                                                        <button type="submit" class="btn btn-sm btn-success">Activate</button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if (($graduate['status'] ?? 'pending') !== 'inactive'): ?>
                                                    <form method="post" action="update_graduate_status.php" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?php echo (int) $graduate['user_id']; ?>">
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
                    <nav class="mt-4" aria-label="Graduate pagination">
                        <ul class="pagination mb-0">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="graduates.php?search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>&page=<?php echo max(1, $page - 1); ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="graduates.php?search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="graduates.php?search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>&page=<?php echo min($totalPages, $page + 1); ?>">Next</a>
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
