<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('employer');
$pageTitle = 'Company Profile';

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
$verificationStatus = $employer['verification_status'] ?? 'Approved';
$logoUrl = !empty($employer['logo']) ? BASE_URL . 'uploads/company_logos/' . htmlspecialchars($employer['logo']) : null;
$jobTitle = $employer['job_title'] ?? null;
$userName = $employer['full_name'] ?? 'Employer';
$userEmail = $employer['email'] ?? '';
$userPhone = $employer['phone'] ?? '';

$jobCount = 0;
$openJobs = 0;
$applicationCount = 0;

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

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1">Company Profile</h1>
                    <p class="text-muted">Review your company details and update your profile whenever your hiring needs change.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="edit_profile.php" class="btn btn-primary-custom">Edit Profile</a>
                    <a href="dashboard.php" class="btn btn-outline-custom">Back to Dashboard</a>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card-ui p-4 bg-white">
                        <div class="d-flex align-items-start gap-3 mb-4">
                            <?php if ($logoUrl): ?>
                                <img src="<?php echo $logoUrl; ?>" alt="Company logo" class="company-logo rounded-circle">
                            <?php else: ?>
                                <div class="company-logo-placeholder rounded-circle d-flex align-items-center justify-content-center bg-light text-primary">
                                    <i class="fas fa-building fa-2x"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h2 class="h5 fw-semibold mb-1"><?php echo htmlspecialchars($companyName ?: 'No company profile yet'); ?></h2>
                                <p class="text-muted mb-1"><?php echo htmlspecialchars($companyIndustry ?: 'Industry not available'); ?></p>
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
                                    <p class="text-uppercase small text-muted mb-2">Founded</p>
                                    <p class="mb-0"><?php echo htmlspecialchars($companyFounded ?: 'Not available'); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h2 class="h6 fw-semibold mb-2">About the Company</h2>
                            <div class="border rounded-4 p-3 bg-light">
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($companyDescription ?: 'Add a company description to help candidates understand your mission and values.')); ?></p>
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
                                <p class="mb-0"><?php echo htmlspecialchars($jobTitle ?: 'Job title not set'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <h2 class="h5 fw-semibold mb-3">Contact Details</h2>
                        <div class="mb-3">
                            <p class="text-uppercase small text-muted mb-2">Name</p>
                            <p class="mb-0"><?php echo htmlspecialchars($userName); ?></p>
                        </div>
                        <div class="mb-3">
                            <p class="text-uppercase small text-muted mb-2">Email</p>
                            <p class="mb-0"><?php echo htmlspecialchars($userEmail); ?></p>
                        </div>
                        <div>
                            <p class="text-uppercase small text-muted mb-2">Phone</p>
                            <p class="mb-0"><?php echo htmlspecialchars($userPhone ?: 'Not added'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <h2 class="h6 fw-semibold mb-3">Active Roles</h2>
                        <p class="mb-0 text-muted"><?php echo htmlspecialchars((string) $openJobs); ?> open jobs</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <h2 class="h6 fw-semibold mb-3">Total Posts</h2>
                        <p class="mb-0 text-muted"><?php echo htmlspecialchars((string) $jobCount); ?> total jobs</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <h2 class="h6 fw-semibold mb-3">Applications</h2>
                        <p class="mb-0 text-muted"><?php echo htmlspecialchars((string) $applicationCount); ?> total applications</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
