<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('employer');
$pageTitle = 'Applicants';
$conn = $GLOBALS['conn'];
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

$employerStmt = $conn->prepare('SELECT company_id FROM employers WHERE user_id = ? LIMIT 1');
$employerStmt->bind_param('i', $userId);
$employerStmt->execute();
$employerResult = $employerStmt->get_result();
$employer = $employerResult->fetch_assoc();
$employerStmt->close();

if (!$employer || empty($employer['company_id'])) {
    $_SESSION['error'] = 'Please complete your company profile before reviewing applicants.';
    redirect('profile.php');
}

$companyId = (int) $employer['company_id'];
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$jobFilter = trim($_GET['job_id'] ?? '');

$jobsStmt = $conn->prepare('SELECT job_id, title FROM jobs WHERE company_id = ? ORDER BY title ASC');
$jobsStmt->bind_param('i', $companyId);
$jobsStmt->execute();
$jobsResult = $jobsStmt->get_result();
$jobs = $jobsResult->fetch_all(MYSQLI_ASSOC);
$jobsStmt->close();

$whereClauses = ['j.company_id = ?'];
$params = [$companyId];
$types = 'i';

if ($search !== '') {
    $whereClauses[] = '(u.full_name LIKE ?)';
    $params[] = '%' . $search . '%';
    $types .= 's';
}

if ($statusFilter !== '' && in_array($statusFilter, ['Pending','Under Review','Shortlisted','Interview Scheduled','Accepted','Rejected'], true)) {
    $whereClauses[] = 'a.status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}

if ($jobFilter !== '' && ctype_digit($jobFilter)) {
    $whereClauses[] = 'a.job_id = ?';
    $params[] = (int) $jobFilter;
    $types .= 'i';
}

$whereSql = implode(' AND ', $whereClauses);

$applicationsStmt = $conn->prepare(
    'SELECT a.application_id, a.job_id, a.status, a.application_date, a.updated_at, u.full_name, j.title AS job_title
     FROM applications a
     INNER JOIN jobs j ON a.job_id = j.job_id
     INNER JOIN graduates g ON a.graduate_id = g.graduate_id
     INNER JOIN users u ON g.user_id = u.user_id
     WHERE ' . $whereSql . '
     ORDER BY a.application_date DESC'
);

$refs = [];
foreach ($params as $key => $value) {
    $refs[$key] = &$params[$key];
}
array_unshift($refs, $types);
call_user_func_array([$applicationsStmt, 'bind_param'], $refs);
$applicationsStmt->execute();
$applicationsResult = $applicationsStmt->get_result();
$applications = $applicationsResult->fetch_all(MYSQLI_ASSOC);
$applicationsStmt->close();

function getEmployerStatusBadge(string $status): string
{
    $badges = [
        'Pending' => 'bg-secondary',
        'Under Review' => 'bg-primary',
        'Shortlisted' => 'bg-info text-dark',
        'Interview Scheduled' => 'bg-warning text-dark',
        'Accepted' => 'bg-success',
        'Rejected' => 'bg-danger',
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
                        <h1 class="h4 fw-bold mb-1">Applicants</h1>
                        <p class="text-muted mb-0">Review candidate applications for your jobs.</p>
                    </div>
                    <a href="dashboard.php" class="btn btn-outline-custom">Back to Dashboard</a>
                </div>

                <form method="get" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Search Applicant</label>
                        <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Applicant name">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Job</label>
                        <select class="form-select" name="job_id">
                            <option value="">All jobs</option>
                            <?php foreach ($jobs as $job): ?>
                                <option value="<?php echo (int) $job['job_id']; ?>"<?php echo $jobFilter === (string) $job['job_id'] ? ' selected' : ''; ?>><?php echo htmlspecialchars($job['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All statuses</option>
                            <option value="Pending"<?php echo $statusFilter === 'Pending' ? ' selected' : ''; ?>>Pending</option>
                            <option value="Under Review"<?php echo $statusFilter === 'Under Review' ? ' selected' : ''; ?>>Under Review</option>
                            <option value="Shortlisted"<?php echo $statusFilter === 'Shortlisted' ? ' selected' : ''; ?>>Shortlisted</option>
                            <option value="Interview Scheduled"<?php echo $statusFilter === 'Interview Scheduled' ? ' selected' : ''; ?>>Interview Scheduled</option>
                            <option value="Accepted"<?php echo $statusFilter === 'Accepted' ? ' selected' : ''; ?>>Accepted</option>
                            <option value="Rejected"<?php echo $statusFilter === 'Rejected' ? ' selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary-custom w-100">Filter</button>
                    </div>
                </form>

                <?php if (empty($applications)): ?>
                    <div class="alert alert-light border">No applicants found for the selected filters.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Applicant</th>
                                    <th>Job</th>
                                    <th>Applied</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $application): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($application['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($application['job_title']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($application['application_date'])); ?></td>
                                        <td><span class="badge <?php echo getEmployerStatusBadge($application['status'] ?? 'Pending'); ?>"><?php echo htmlspecialchars($application['status'] ?? 'Pending'); ?></span></td>
                                        <td><a href="application_details.php?application_id=<?php echo (int) $application['application_id']; ?>" class="btn btn-sm btn-outline-custom">Review</a></td>
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

<?php include __DIR__ . '/../includes/footer.php'; ?>
