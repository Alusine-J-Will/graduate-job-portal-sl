<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');
$pageTitle = 'My Applications';
$conn = $GLOBALS['conn'];
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

$graduateStmt = $conn->prepare('SELECT graduate_id FROM graduates WHERE user_id = ? LIMIT 1');
$graduateStmt->bind_param('i', $userId);
$graduateStmt->execute();
$graduateResult = $graduateStmt->get_result();
$graduate = $graduateResult->fetch_assoc();
$graduateStmt->close();

if (!$graduate) {
    $_SESSION['error'] = 'Please complete your graduate profile before viewing applications.';
    redirect('profile.php');
}

$graduateId = (int) $graduate['graduate_id'];

$applicationsStmt = $conn->prepare(
    'SELECT a.application_id, a.job_id, a.status, a.application_date, a.updated_at, a.cover_letter_path, j.title, c.company_name
     FROM applications a
     INNER JOIN jobs j ON a.job_id = j.job_id
     LEFT JOIN companies c ON j.company_id = c.company_id
     WHERE a.graduate_id = ?
     ORDER BY a.application_date DESC'
);
$applicationsStmt->bind_param('i', $graduateId);
$applicationsStmt->execute();
$applicationsResult = $applicationsStmt->get_result();
$applications = $applicationsResult->fetch_all(MYSQLI_ASSOC);
$applicationsStmt->close();

function getStatusBadge(string $status): string
{
    $status = normalizeApplicationStatus($status);
    $badges = [
        'pending' => 'bg-secondary',
        'under_review' => 'bg-primary',
        'shortlisted' => 'bg-info text-dark',
        'interview_scheduled' => 'bg-warning text-dark',
        'accepted' => 'bg-success',
        'rejected' => 'bg-danger',
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
                        <h1 class="h4 fw-bold mb-1">My Applications</h1>
                        <p class="text-muted mb-0">Track the roles you have applied for and review their progress.</p>
                    </div>
                    <a href="jobs.php" class="btn btn-outline-custom">Browse Jobs</a>
                </div>

                <?php if (empty($applications)): ?>
                    <div class="alert alert-light border">You have not submitted any applications yet.</div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($applications as $application): ?>
                            <div class="col-12">
                                <div class="border rounded-4 p-4">
                                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                                        <div>
                                            <h2 class="h5 fw-semibold mb-1"><?php echo htmlspecialchars($application['title']); ?></h2>
                                            <p class="text-muted mb-1"><?php echo htmlspecialchars($application['company_name'] ?: 'Company not listed'); ?></p>
                                            <p class="small text-muted mb-0">Applied: <?php echo date('d M Y', strtotime($application['application_date'])); ?></p>
                                        </div>
                                        <div class="text-md-end">
                                            <span class="badge <?php echo getStatusBadge($application['status'] ?? 'pending'); ?> mb-2"><?php echo htmlspecialchars(getApplicationStatusLabel($application['status'] ?? 'pending')); ?></span>
                                            <p class="small text-muted mb-0">Updated: <?php echo date('d M Y', strtotime($application['updated_at'])); ?></p>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="application_details.php?application_id=<?php echo (int) $application['application_id']; ?>" class="btn btn-outline-custom">View Details</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
