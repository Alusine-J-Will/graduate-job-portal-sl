<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');
$pageTitle = 'Browse Jobs';
$conn = $GLOBALS['conn'];
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

$graduateId = getGraduateIdByUserId($conn, $userId);
if (!$graduateId) {
    $_SESSION['error'] = 'Please complete your graduate profile before browsing jobs.';
    redirect('profile.php');
}

$search = trim($_GET['search'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');
$employmentFilter = trim($_GET['employment_type'] ?? '');
$experienceFilter = trim($_GET['experience_level'] ?? '');
$locationFilter = trim($_GET['location'] ?? '');
$sort = $_GET['sort'] ?? 'recent';
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$categoriesStmt = $conn->prepare('SELECT category_id, category_name FROM job_categories ORDER BY category_name ASC');
$categoriesStmt->execute();
$categoriesResult = $categoriesStmt->get_result();
$categories = $categoriesResult->fetch_all(MYSQLI_ASSOC);
$categoriesStmt->close();

$employmentTypes = ['Full Time', 'Part Time', 'Contract', 'Internship', 'Temporary'];
$experienceLevels = ['Entry Level', 'Mid Level', 'Senior Level', 'Manager', 'Director'];

$whereClauses = ["j.status = 'Open'", '(j.deadline IS NULL OR j.deadline >= CURDATE())'];
$params = [];
$types = '';

if ($search !== '') {
    $searchTerm = '%' . $search . '%';
    $whereClauses[] = '(j.title LIKE ? OR c.company_name LIKE ? OR jc.category_name LIKE ? OR j.location LIKE ? OR j.description LIKE ?)';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sssss';
}

if ($categoryFilter !== '' && ctype_digit($categoryFilter)) {
    $whereClauses[] = 'j.category_id = ?';
    $params[] = (int) $categoryFilter;
    $types .= 'i';
}

if ($employmentFilter !== '' && in_array($employmentFilter, $employmentTypes, true)) {
    $whereClauses[] = 'j.employment_type = ?';
    $params[] = $employmentFilter;
    $types .= 's';
}

if ($experienceFilter !== '' && in_array($experienceFilter, $experienceLevels, true)) {
    $whereClauses[] = 'j.experience_level = ?';
    $params[] = $experienceFilter;
    $types .= 's';
}

if ($locationFilter !== '') {
    $locationTerm = '%' . $locationFilter . '%';
    $whereClauses[] = 'j.location LIKE ?';
    $params[] = $locationTerm;
    $types .= 's';
}

$whereSql = implode(' AND ', $whereClauses);

$countSql = 'SELECT COUNT(*) AS total
             FROM jobs j
             LEFT JOIN companies c ON j.company_id = c.company_id
             LEFT JOIN job_categories jc ON j.category_id = jc.category_id
             WHERE ' . $whereSql;
$countStmt = $conn->prepare($countSql);
if (!empty($params)) {
    $bindValues = $params;
    $refs = [];
    foreach ($bindValues as $key => $value) {
        $refs[$key] = &$bindValues[$key];
    }
    array_unshift($refs, $types);
    call_user_func_array([$countStmt, 'bind_param'], $refs);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalJobs = (int) ($countResult->fetch_assoc()['total'] ?? 0);
$countStmt->close();

$totalPages = max(1, (int) ceil($totalJobs / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$orderBy = 'j.created_at DESC';
switch ($sort) {
    case 'oldest':
        $orderBy = 'j.created_at ASC';
        break;
    case 'deadline':
        $orderBy = 'j.deadline IS NULL, j.deadline ASC';
        break;
    case 'company':
        $orderBy = 'c.company_name ASC';
        break;
    default:
        $orderBy = 'j.created_at DESC';
        break;
}

$listSql = 'SELECT j.job_id, j.title, c.company_name, jc.category_name, j.location, j.employment_type, j.experience_level, j.salary, j.deadline, j.created_at, CASE WHEN sj.job_id IS NOT NULL THEN 1 ELSE 0 END AS is_saved
            FROM jobs j
            LEFT JOIN companies c ON j.company_id = c.company_id
            LEFT JOIN job_categories jc ON j.category_id = jc.category_id
            LEFT JOIN saved_jobs sj ON sj.job_id = j.job_id AND sj.graduate_id = ?
            WHERE ' . $whereSql . '
            ORDER BY ' . $orderBy . '
            LIMIT ? OFFSET ?';

$listStmt = $conn->prepare($listSql);
$bindValues = [$graduateId];
$bindValues = array_merge($bindValues, $params);
$bindValues[] = $perPage;
$bindValues[] = $offset;
$bindTypes = 'i' . $types . 'ii';
$refs = [];
foreach ($bindValues as $key => $value) {
    $refs[$key] = &$bindValues[$key];
}
array_unshift($refs, $bindTypes);
call_user_func_array([$listStmt, 'bind_param'], $refs);
$listStmt->execute();
$listResult = $listStmt->get_result();
$jobs = $listResult->fetch_all(MYSQLI_ASSOC);
$listStmt->close();

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
                        <h1 class="h4 fw-bold mb-1">Browse Jobs</h1>
                        <p class="text-muted mb-0">Discover open opportunities and save the roles that match your goals.</p>
                    </div>
                    <a href="saved_jobs.php" class="btn btn-outline-custom">My Saved Jobs</a>
                </div>

                <form method="get" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="search" class="form-label fw-semibold">Search</label>
                        <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Job title, company, keyword">
                    </div>
                    <div class="col-md-2">
                        <label for="category" class="form-label fw-semibold">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo (int) $category['category_id']; ?>"<?php echo ($categoryFilter === (string) $category['category_id']) ? ' selected' : ''; ?>><?php echo htmlspecialchars($category['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="employment_type" class="form-label fw-semibold">Type</label>
                        <select class="form-select" id="employment_type" name="employment_type">
                            <option value="">All</option>
                            <?php foreach ($employmentTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>"<?php echo ($employmentFilter === $type) ? ' selected' : ''; ?>><?php echo htmlspecialchars($type); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="experience_level" class="form-label fw-semibold">Experience</label>
                        <select class="form-select" id="experience_level" name="experience_level">
                            <option value="">All</option>
                            <?php foreach ($experienceLevels as $level): ?>
                                <option value="<?php echo htmlspecialchars($level); ?>"<?php echo ($experienceFilter === $level) ? ' selected' : ''; ?>><?php echo htmlspecialchars($level); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="location" class="form-label fw-semibold">Location</label>
                        <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($locationFilter); ?>" placeholder="City">
                    </div>
                    <div class="col-md-2">
                        <label for="sort" class="form-label fw-semibold">Sort</label>
                        <select class="form-select" id="sort" name="sort">
                            <option value="recent"<?php echo ($sort === 'recent') ? ' selected' : ''; ?>>Most Recent</option>
                            <option value="oldest"<?php echo ($sort === 'oldest') ? ' selected' : ''; ?>>Oldest</option>
                            <option value="deadline"<?php echo ($sort === 'deadline') ? ' selected' : ''; ?>>Deadline</option>
                            <option value="company"<?php echo ($sort === 'company') ? ' selected' : ''; ?>>Company Name</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary-custom w-100">Apply Filters</button>
                    </div>
                </form>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="text-muted mb-0">Showing <?php echo count($jobs); ?> of <?php echo $totalJobs; ?> jobs</p>
                    <a href="jobs.php" class="btn btn-sm btn-outline-custom">Reset</a>
                </div>

                <?php if (empty($jobs)): ?>
                    <div class="alert alert-light text-center py-4 mb-0">No open jobs match your current filters.</div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($jobs as $job): ?>
                            <div class="col-12">
                                <div class="border rounded-4 p-4 h-100">
                                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                                        <div>
                                            <h2 class="h5 fw-semibold mb-1"><?php echo htmlspecialchars($job['title']); ?></h2>
                                            <p class="text-muted mb-1"><?php echo htmlspecialchars($job['company_name'] ?: 'Company not listed'); ?></p>
                                            <p class="small text-muted mb-0"><?php echo htmlspecialchars($job['category_name'] ?: 'Uncategorized'); ?> • <?php echo htmlspecialchars($job['location']); ?></p>
                                        </div>
                                        <div class="text-md-end">
                                            <p class="small text-muted mb-1">Posted <?php echo date('d M Y', strtotime($job['created_at'])); ?></p>
                                            <p class="small text-muted mb-0">Deadline <?php echo !empty($job['deadline']) ? date('d M Y', strtotime($job['deadline'])) : 'Open'; ?></p>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-3"><strong>Type:</strong> <?php echo htmlspecialchars($job['employment_type'] ?: 'Not provided'); ?></div>
                                        <div class="col-md-3"><strong>Experience:</strong> <?php echo htmlspecialchars($job['experience_level'] ?: 'Not provided'); ?></div>
                                        <div class="col-md-3"><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary'] ?: 'Not specified'); ?></div>
                                        <div class="col-md-3"><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></div>
                                    </div>

                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="job_details.php?job_id=<?php echo (int) $job['job_id']; ?>" class="btn btn-outline-custom">View Details</a>
                                        <a href="apply_job.php?job_id=<?php echo (int)$job['job_id']; ?>"
                                            class="btn btn-primary-custom">
                                            Apply
                                        </a>
                                        <?php if ((int) $job['is_saved'] === 1): ?>
                                            <button type="button" class="btn btn-outline-success" disabled>Saved</button>
                                        <?php else: ?>
                                            <form method="post" action="save_job.php" class="d-inline">
                                                <input type="hidden" name="job_id" value="<?php echo (int) $job['job_id']; ?>">
                                                <input type="hidden" name="redirect_to" value="jobs.php">
                                                <button type="submit" class="btn btn-primary-custom">Save Job</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($totalPages > 1): ?>
                    <nav class="mt-4" aria-label="Job pagination">
                        <ul class="pagination justify-content-center">
                            <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                                <?php
                                $queryParams = [
                                    'search' => $search,
                                    'category' => $categoryFilter,
                                    'employment_type' => $employmentFilter,
                                    'experience_level' => $experienceFilter,
                                    'location' => $locationFilter,
                                    'sort' => $sort,
                                    'page' => $pageNumber,
                                ];
                                $queryString = http_build_query(array_filter($queryParams, static function ($value) {
                                    return $value !== '';
                                }));
                                ?>
                                <li class="page-item <?php echo $pageNumber === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="jobs.php?<?php echo $queryString; ?>"><?php echo $pageNumber; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
