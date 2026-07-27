<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('employer');
$pageTitle = 'Edit Company Profile';

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
$companyName = sanitizeInput($employer['company_name'] ?? '');
$companyLocation = sanitizeInput($employer['company_location'] ?? '');
$companyIndustry = sanitizeInput($employer['industry'] ?? '');
$companySize = sanitizeInput($employer['company_size'] ?? '');
$companyWebsite = sanitizeInput($employer['website'] ?? '');
$companyDescription = sanitizeInput($employer['description'] ?? '');
$companyFounded = sanitizeInput($employer['founded_year'] ?? '');
$jobTitle = sanitizeInput($employer['job_title'] ?? '');
$fullName = sanitizeInput($employer['full_name'] ?? '');
$email = sanitizeInput($employer['email'] ?? '');
$phone = sanitizeInput($employer['phone'] ?? '');

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

            <div class="card-ui p-4 mb-4 bg-white">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <div>
                        <h2 class="h5 fw-semibold mb-1">Edit Company Profile</h2>
                        <p class="text-muted mb-0">Keep your company details up to date for candidates and hiring managers.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="profile.php" class="btn btn-outline-custom">Back to Profile</a>
                    </div>
                </div>
            </div>

            <div class="card-ui p-4 bg-white">
                <form action="update_profile.php" method="POST" novalidate>
                    <input type="hidden" name="company_id" value="<?php echo htmlspecialchars((string) $companyId); ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo $companyName; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="industry" class="form-label">Industry</label>
                            <input type="text" class="form-control" id="industry" name="industry" value="<?php echo $companyIndustry; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="company_size" class="form-label">Company Size</label>
                            <input type="text" class="form-control" id="company_size" name="company_size" value="<?php echo $companySize; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="company_location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="company_location" name="company_location" value="<?php echo $companyLocation; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="website" class="form-label">Website</label>
                            <input type="url" class="form-control" id="website" name="website" value="<?php echo $companyWebsite; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="founded_year" class="form-label">Founded Year</label>
                            <input type="number" class="form-control" id="founded_year" name="founded_year" value="<?php echo $companyFounded; ?>" min="1800" max="2099">
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Company Description</label>
                            <textarea class="form-control" id="description" name="description" rows="5"><?php echo $companyDescription; ?></textarea>
                            <div class="form-text">Share your mission and what makes your company a great place to work.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="job_title" class="form-label">Hiring Contact Title</label>
                            <input type="text" class="form-control" id="job_title" name="job_title" value="<?php echo $jobTitle; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Contact Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $phone; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="full_name" class="form-label">Contact Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo $fullName; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Contact Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" readonly>
                        </div>
                    </div>

                    <div class="mt-4 d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-3">
                        <button type="submit" class="btn btn-primary-custom">Save Changes</button>
                        <a href="profile.php" class="btn btn-outline-custom">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
