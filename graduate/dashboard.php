<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');
$pageTitle = 'Graduate Dashboard';

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

$conn = $GLOBALS['conn'];

$userStmt = $conn->prepare('SELECT user_id, full_name, email, phone FROM users WHERE user_id = ? LIMIT 1');
$userStmt->bind_param('i', $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();
$userStmt->close();

$graduateStmt = $conn->prepare('SELECT graduate_id, location, bio, cv, profile_picture FROM graduates WHERE user_id = ? LIMIT 1');
$graduateStmt->bind_param('i', $userId);
$graduateStmt->execute();
$graduateResult = $graduateStmt->get_result();
$graduate = $graduateResult->fetch_assoc();
$graduateStmt->close();

$profilePicture = !empty($graduate['profile_picture']) ? $graduate['profile_picture'] : null;
$dashboardAvatar = $profilePicture;
$location = !empty($graduate['location']) ? $graduate['location'] : 'Location not added.';
$bio = !empty($graduate['bio']) ? $graduate['bio'] : 'Add a short bio to introduce yourself to employers.';
$cv = !empty($graduate['cv']);
$greeting = getTimeOfDayGreeting($user['full_name'] ?? 'Graduate');

$completionFields = [
    'profile_picture' => !empty($graduate['profile_picture']),
    'location' => !empty($graduate['location']),
    'bio' => !empty($graduate['bio']),
    'cv' => $cv,
];

$completedFields = count(array_filter($completionFields));
$completionPercentage = (int) round(($completedFields / count($completionFields)) * 100);

$stats = [
    ['icon' => 'fas fa-file-alt', 'value' => '0', 'label' => 'Applications Submitted'],
    ['icon' => 'fas fa-bookmark', 'value' => '0', 'label' => 'Saved Jobs'],
    ['icon' => 'fas fa-comments', 'value' => '0', 'label' => 'Interview Invitations'],
    ['icon' => 'fas fa-eye', 'value' => 'Coming Soon', 'label' => 'Profile Views'],
];

$recentApplications = [];

$recommendedJobs = [
    ['title' => 'Junior Web Developer', 'company' => 'Sierra Digital Labs', 'location' => 'Freetown', 'type' => 'Full-Time'],
    ['title' => 'Customer Support Associate', 'company' => 'EcoTech Sierra Leone', 'location' => 'Makeni', 'type' => 'Part-Time'],
    ['title' => 'Data Entry Officer', 'company' => 'National Savings Bank', 'location' => 'Bo', 'type' => 'Contract'],
];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/dashboard_topbar.php';
?>

<div class="container-fluid py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <button id="sidebarToggle" class="btn btn-outline-custom d-lg-none mb-3">
                <i class="fas fa-bars me-2"></i>Menu
            </button>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1"><?php echo htmlspecialchars($greeting); ?></h1>
                    <p class="text-muted">Ready to discover your next opportunity today?</p>
                </div>
                <a href="profile.php" class="btn btn-primary-custom">Complete Profile</a>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <?php if ($profilePicture): ?>
                                <img src="<?php echo BASE_URL; ?>uploads/profile_photos/<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile photo" class="profile-avatar rounded-circle">
                            <?php else: ?>
                                <div class="profile-avatar default-avatar rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user fa-2x text-white"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h2 class="h5 fw-semibold mb-1"><?php echo htmlspecialchars($user['full_name'] ?? 'Graduate'); ?></h2>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                            </div>
                        </div>
                        <ul class="list-unstyled mb-0 text-muted small">
                            <li class="mb-2"><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($user['phone'] ?? 'Phone not added.'); ?></li>
                            <li><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($location); ?></li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-6 col-xl-8">
                    <div class="card-ui p-4 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h2 class="h5 fw-semibold mb-1">Profile Completion</h2>
                                <p class="text-muted mb-0">Complete your profile to improve visibility with employers.</p>
                            </div>
                            <span class="badge bg-primary bg-opacity-10 text-primary py-2 px-3 fw-semibold"><?php echo $completionPercentage; ?>%</span>
                        </div>
                        <div class="progress mb-3" style="height: 12px; border-radius: 999px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $completionPercentage; ?>%;" aria-valuenow="<?php echo $completionPercentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <a href="profile.php" class="btn btn-outline-custom">Complete Profile</a>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <?php foreach ($stats as $stat): ?>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card-ui p-4 bg-white h-100">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h3 class="h6 text-muted mb-1"><?php echo htmlspecialchars($stat['label']); ?></h3>
                                    <p class="h4 fw-bold mb-0"><?php echo htmlspecialchars($stat['value']); ?></p>
                                </div>
                                <div class="rounded-4 bg-primary bg-opacity-10 text-primary p-3">
                                    <i class="<?php echo htmlspecialchars($stat['icon']); ?>"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card-ui p-4 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h5 fw-semibold mb-0">Recent Applications</h2>
                            <a href="applications.php" class="btn btn-sm btn-outline-custom">View All</a>
                        </div>
                        <?php if (empty($recentApplications)): ?>
                            <p class="text-muted mb-0">No applications yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Job Title</th>
                                            <th>Company</th>
                                            <th>Status</th>
                                            <th>Applied Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentApplications as $application): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($application['job_title']); ?></td>
                                                <td><?php echo htmlspecialchars($application['company']); ?></td>
                                                <td><?php echo htmlspecialchars($application['status']); ?></td>
                                                <td><?php echo htmlspecialchars($application['applied_date']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-bell text-primary me-2"></i>
                            <h2 class="h5 fw-semibold mb-0">Notifications</h2>
                        </div>
                        <p class="text-muted mb-0">No new notifications.</p>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card-ui p-4 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h5 fw-semibold mb-0">Recommended Jobs</h2>
                            <a href="jobs.php" class="btn btn-sm btn-outline-custom">Search Jobs</a>
                        </div>
                        <div class="row g-3">
                            <?php foreach ($recommendedJobs as $job): ?>
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100">
                                        <h3 class="h6 fw-semibold mb-2"><?php echo htmlspecialchars($job['title']); ?></h3>
                                        <p class="text-muted mb-1"><?php echo htmlspecialchars($job['company']); ?></p>
                                        <p class="small text-muted mb-3"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($job['location']); ?> • <?php echo htmlspecialchars($job['type']); ?></p>
                                        <a href="#" class="btn btn-sm btn-outline-custom">View Details</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p class="small text-muted mt-3 mb-0">Placeholder content ready for future database-driven job recommendations.</p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card-ui p-4 bg-white h-100">
                        <h2 class="h5 fw-semibold mb-3">Quick Actions</h2>
                        <div class="d-grid gap-2">
                            <a href="jobs.php" class="btn btn-outline-custom">Search Jobs</a>
                            <a href="applications.php" class="btn btn-outline-custom">My Applications</a>
                            <a href="profile.php" class="btn btn-outline-custom">Complete Profile</a>
                            <a href="profile.php" class="btn btn-outline-custom">Upload CV</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
