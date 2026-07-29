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
    $_SESSION['error'] = 'Unable to manage jobs without a linked company profile.';
    redirect('profile.php');
}

$searchTitle = trim($_GET['search_title'] ?? '');
$searchCategory = trim($_GET['search_category'] ?? '');
$searchStatus = trim($_GET['search_status'] ?? '');

$whereClauses = ['company_id = ?'];
$params = [$companyId];
$paramTypes = 'i';

if ($searchTitle !== '') {
    $whereClauses[] = 'title LIKE ?';
    $params[] = '%' . $searchTitle . '%';
    $paramTypes .= 's';
}

if (ctype_digit($searchCategory)) {
    $whereClauses[] = 'category_id = ?';
    $params[] = $searchCategory;
    $paramTypes .= 'i';
}

$validStatuses = ['Draft', 'Open', 'Closed', 'Expired'];
if (in_array($searchStatus, $validStatuses, true)) {
    $whereClauses[] = 'status = ?';
    $params[] = $searchStatus;
    $paramTypes .= 's';
}

$whereSql = implode(' AND ', $whereClauses);
$query = 
    'SELECT j.job_id, j.title, jc.category_name, j.employment_type, j.location, j.status, j.created_at, COUNT(a.application_id) AS application_count
     FROM jobs j
     LEFT JOIN job_categories jc ON j.category_id = jc.category_id
     LEFT JOIN applications a ON j.job_id = a.job_id
     WHERE ' . $whereSql . '
     GROUP BY j.job_id, j.title, jc.category_name, j.employment_type, j.location, j.status, j.created_at
     ORDER BY j.created_at DESC';

$stmt = $conn->prepare($query);
$stmt->bind_param($paramTypes, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$jobs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$categoriesStmt = $conn->prepare('SELECT category_id, category_name FROM job_categories ORDER BY category_name ASC');
$categoriesStmt->execute();
$categoriesResult = $categoriesStmt->get_result();
$categories = $categoriesResult->fetch_all(MYSQLI_ASSOC);
$categoriesStmt->close();

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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h1 class="h4 fw-bold mb-1">Manage Jobs</h1>
                        <p class="text-muted mb-0">Search, filter, and review the job postings created by your company.</p>
                    </div>
                    <a href="post_job.php" class="btn btn-primary-custom">Post New Job</a>
                </div>

                <form class="row g-3 align-items-end mb-4" method="get" action="manage_jobs.php">
                    <div class="col-md-4">
                        <label for="search_title" class="form-label fw-semibold">Job Title</label>
                        <input type="text" class="form-control" id="search_title" name="search_title" value="<?php echo htmlspecialchars($searchTitle); ?>" placeholder="Search by title">
                    </div>
                    <div class="col-md-3">
                        <label for="search_category" class="form-label fw-semibold">Category</label>
                        <select class="form-select" id="search_category" name="search_category">
                            <option value="">All categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['category_id']); ?>"<?php echo ($searchCategory == $category['category_id']) ? ' selected' : ''; ?>><?php echo htmlspecialchars($category['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="search_status" class="form-label fw-semibold">Status</label>
                        <select class="form-select" id="search_status" name="search_status">
                            <option value="">All statuses</option>
                            <?php foreach ($validStatuses as $status): ?>
                                <option value="<?php echo htmlspecialchars($status); ?>"<?php echo ($searchStatus === $status) ? ' selected' : ''; ?>><?php echo htmlspecialchars($status); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary-custom w-100">Search</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Job Title</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Applications</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($jobs)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No jobs found. Use the filters or create a new job.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($jobs as $job): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($job['title']); ?></td>
                                        <td><?php echo htmlspecialchars($job['category_name'] ?? 'Uncategorized'); ?></td>
                                        <td><?php echo htmlspecialchars($job['employment_type']); ?></td>
                                        <td><?php echo htmlspecialchars($job['location']); ?></td>
                                        <td><span class="badge bg-<?php echo $job['status'] === 'Open' ? 'success' : 'secondary'; ?>"><?php echo htmlspecialchars($job['status']); ?></span></td>
                                        <td><?php echo htmlspecialchars((string) ($job['application_count'] ?? 0)); ?></td>
                                        <td><?php echo date('d M Y', strtotime($job['created_at'])); ?></td>
                                        <td>
                                            <a href="view_job.php?job_id=<?php echo htmlspecialchars($job['job_id']); ?>" class="btn btn-sm btn-outline-custom me-1">View</a>
                                            <button type="button" class="btn btn-sm btn-outline-secondary me-1" disabled>Edit</button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" disabled>Delete</button>
                                        </td>
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

<?php include __DIR__ . '/../includes/footer.php'; ?>
