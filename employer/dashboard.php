<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('employer');
$pageTitle = 'Employer Dashboard';

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$conn = $GLOBALS['conn'];

$employerStmt = $conn->prepare(
    'SELECT e.employer_id, e.company_id, e.job_title, u.full_name, u.email, u.phone, c.company_name, c.logo, c.industry, c.company_size, c.location AS company_location, c.website, c.description, c.founded_year, c.verification_status
     FROM employers e
     LEFT JOIN companies c ON e.company_id = c.company_id
     LEFT JOIN users u ON e.user_id = u.user_id
     WHERE e.user_id = ?
     LIMIT 1'
);
$employerStmt->bind_param('i', $userId);
$employerStmt->execute();
$employerResult = $employerStmt->get_result();
$employer = $employerResult->fetch_assoc();
$employerStmt->close();

$companyId = $employer['company_id'] ?? null;
$companyName = $employer['company_name'] ?? null;
$companyLocation = $employer['company_location'] ?? null;
$companyIndustry = $employer['industry'] ?? null;
$companySize = $employer['company_size'] ?? null;
$companyWebsite = $employer['website'] ?? null;
$companyDescription = $employer['description'] ?? null;
$companyFounded = $employer['founded_year'] ?? null;
$verificationStatus = $employer['verification_status'] ?? 'Pending';
$logoUrl = !empty($employer['logo']) ? BASE_URL . 'uploads/company_logos/' . htmlspecialchars($employer['logo']) : null;
$jobTitle = $employer['job_title'] ?? null;
$userName = $employer['full_name'] ?? 'Employer';
$userEmail = $employer['email'] ?? '';
$userPhone = $employer['phone'] ?? '';

$companyProfileComplete = !empty($companyName) && !empty($companyLocation) && !empty($companyIndustry) && !empty($companyDescription);
$completionPercentage = (int) round((count(array_filter([
    !empty($companyName),
    !empty($companyLocation),
    !empty($companyIndustry),
    !empty($companyDescription),
    !empty($companyWebsite),
    !empty($jobTitle),
])) / 6) * 100);

$jobCount = 0;
$openJobs = 0;
$draftJobs = 0;
$applicationCount = 0;
$recentJobs = [];

if ($companyId) {
    $jobCountStmt = $conn->prepare('SELECT COUNT(*) FROM jobs WHERE company_id = ?');
    $jobCountStmt->bind_param('i', $companyId);
    $jobCountStmt->execute();
    $jobCountStmt->bind_result($jobCount);
    $jobCountStmt->fetch();
    $jobCountStmt->close();

    $openJobsStmt = $conn->prepare('SELECT COUNT(*) FROM jobs WHERE company_id = ? AND status = ?');
    $statusOpen = 'Open';
    $openJobsStmt->bind_param('is', $companyId, $statusOpen);
    $openJobsStmt->execute();
    $openJobsStmt->bind_result($openJobs);
    $openJobsStmt->fetch();
    $openJobsStmt->close();

    $draftJobsStmt = $conn->prepare('SELECT COUNT(*) FROM jobs WHERE company_id = ? AND status = ?');
    $statusDraft = 'Draft';
    $draftJobsStmt->bind_param('is', $companyId, $statusDraft);
    $draftJobsStmt->execute();
    $draftJobsStmt->bind_result($draftJobs);
    $draftJobsStmt->fetch();
    $draftJobsStmt->close();

    $applicationStmt = $conn->prepare(
        'SELECT COUNT(*)
         FROM applications a
         INNER JOIN jobs j ON a.job_id = j.job_id
         WHERE j.company_id = ?'
    );
    $applicationStmt->bind_param('i', $companyId);
    $applicationStmt->execute();
    $applicationStmt->bind_result($applicationCount);
    $applicationStmt->fetch();
    $applicationStmt->close();

    $recentJobsStmt = $conn->prepare(
        'SELECT title, status, location, deadline
         FROM jobs
         WHERE company_id = ?
         ORDER BY created_at DESC
         LIMIT 3'
    );
    $recentJobsStmt->bind_param('i', $companyId);
    $recentJobsStmt->execute();
    $recentJobsResult = $recentJobsStmt->get_result();
    $recentJobs = $recentJobsResult->fetch_all(MYSQLI_ASSOC);
    $recentJobsStmt->close();
}

$greeting = getTimeOfDayGreeting($userName);

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
                    <h1 class="h3 fw-bold mb-1"><?php echo htmlspecialchars($greeting); ?></h1>
                    <p class="text-muted">Manage your company profile, open positions, and candidate interest.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="profile.php" class="btn btn-outline-custom">Company Profile</a>
                    <a href="edit_profile.php" class="btn btn-primary-custom">Edit Profile</a>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card-ui p-4 bg-white h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <p class="text-uppercase small text-muted mb-2">Open Jobs</p>
                                <h2 class="h4 mb-0"><?php echo htmlspecialchars((string) $openJobs); ?></h2>
                            </div>
                            <i class="fas fa-briefcase fa-2x text-primary"></i>
                        </div>
                        <p class="text-muted mb-0">Live roles currently accepting applications.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card-ui p-4 bg-white h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <p class="text-uppercase small text-muted mb-2">Total Jobs</p>
                                <h2 class="h4 mb-0"><?php echo htmlspecialchars((string) $jobCount); ?></h2>
                            </div>
                            <i class="fas fa-list fa-2x text-primary"></i>
                        </div>
                        <p class="text-muted mb-0">Total roles posted by your company.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card-ui p-4 bg-white h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <p class="text-uppercase small text-muted mb-2">Draft Jobs</p>
                                <h2 class="h4 mb-0"><?php echo htmlspecialchars((string) $draftJobs); ?></h2>
                            </div>
                            <i class="fas fa-file-alt fa-2x text-primary"></i>
                        </div>
                        <p class="text-muted mb-0">Roles saved as drafts and not yet published.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card-ui p-4 bg-white h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <p class="text-uppercase small text-muted mb-2">Applications</p>
                                <h2 class="h4 mb-0"><?php echo htmlspecialchars((string) $applicationCount); ?></h2>
                            </div>
                            <i class="fas fa-file-alt fa-2x text-primary"></i>
                        </div>
                        <p class="text-muted mb-0">Applications submitted to your open positions.</p>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card-ui p-4 bg-white h-100">
                        <div class="d-flex align-items-start gap-3 mb-4">
                            <?php if ($logoUrl): ?>
                                <img src="<?php echo $logoUrl; ?>" alt="Company logo" class="company-logo rounded-circle">
                            <?php else: ?>
                                <div class="company-logo-placeholder rounded-circle d-flex align-items-center justify-content-center bg-light text-primary">
                                    <i class="fas fa-building fa-2x"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h2 class="h5 fw-semibold mb-1"><?php echo htmlspecialchars($companyName ?: 'Company Profile'); ?></h2>
                                <p class="text-muted mb-1"><?php echo htmlspecialchars($companyIndustry ?: 'Industry not specified'); ?></p>
                                <span class="badge bg-<?php echo $verificationStatus === 'Approved' ? 'success' : ($verificationStatus === 'Rejected' ? 'danger' : 'secondary'); ?>"><?php echo htmlspecialchars($verificationStatus); ?></span>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 bg-light">
                                    <p class="text-uppercase small text-muted mb-2">Company Name</p>
                                    <p class="mb-0"><?php echo htmlspecialchars($companyName ?: 'Not added yet'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 bg-light">
                                    <p class="text-uppercase small text-muted mb-2">Location</p>
                                    <p class="mb-0"><?php echo htmlspecialchars($companyLocation ?: 'Not added yet'); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 bg-light">
                                    <p class="text-uppercase small text-muted mb-2">Company Size</p>
                                    <p class="mb-0"><?php echo htmlspecialchars($companySize ?: 'Not available'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 bg-light">
                                    <p class="text-uppercase small text-muted mb-2">Founded Year</p>
                                    <p class="mb-0"><?php echo htmlspecialchars($companyFounded ?: 'Not available'); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h3 class="h6 fw-semibold mb-2">About the Company</h3>
                            <div class="border rounded-4 p-3 bg-light">
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($companyDescription ?: 'Add a company description to help candidates learn more.')); ?></p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <p class="text-uppercase small text-muted mb-2">Website</p>
                                <?php if ($companyWebsite): ?>
                                    <a href="<?php echo htmlspecialchars($companyWebsite); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($companyWebsite); ?></a>
                                <?php else: ?>
                                    <p class="mb-0 text-muted">Not added</p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <p class="text-uppercase small text-muted mb-2">Hiring Contact</p>
                                <p class="mb-0"><?php echo htmlspecialchars($jobTitle ?: 'Job title not added'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <h2 class="h5 fw-semibold mb-3">Quick Actions</h2>
                        <div class="d-grid gap-2">
                            <a href="edit_profile.php" class="btn btn-primary-custom">Update Company Profile</a>
                            <a href="post_job.php" class="btn btn-outline-custom">Post a Job</a>
                            <a href="manage_jobs.php" class="btn btn-outline-custom">Manage Jobs</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-ui p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="h5 fw-semibold mb-1">Recent Job Postings</h2>
                        <p class="text-muted mb-0">See your latest roles and status updates.</p>
                    </div>
                    <a href="manage_jobs.php" class="btn btn-sm btn-outline-custom">View All</a>
                </div>

                <?php if (empty($recentJobs)): ?>
                    <p class="text-muted mb-0">No recent job postings yet. Create a job posting to see it listed here.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Deadline</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentJobs as $job): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($job['title']); ?></td>
                                        <td><?php echo htmlspecialchars($job['location']); ?></td>
                                        <td><?php echo htmlspecialchars($job['status']); ?></td>
                                        <td><?php echo htmlspecialchars($job['deadline'] ?: 'N/A'); ?></td>
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

<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
