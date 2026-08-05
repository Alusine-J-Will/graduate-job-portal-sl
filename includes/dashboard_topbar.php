<?php
/**
 * Authenticated dashboard topbar.
 */

if (!isset($pageTitle)) {
    $pageTitle = ' Dashboard';
}

$loggedInName = $_SESSION['full_name'] ?? 'User';

if (!isset($dashboardAvatar) || $dashboardAvatar === null) {
    $dashboardAvatar = null;
    if (isset($_SESSION['user_id'], $_SESSION['role']) && $_SESSION['role'] === 'graduate') {
        $conn = $GLOBALS['conn'];
        $stmt = $conn->prepare('SELECT profile_picture FROM graduates WHERE user_id = ? LIMIT 1');
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $stmt->bind_result($dashboardAvatar);
        $stmt->fetch();
        $stmt->close();
    }
}

$avatarUrl = !empty($dashboardAvatar) ? BASE_URL . 'uploads/profile_photos/' . htmlspecialchars($dashboardAvatar) : null;

$logoLink = BASE_URL;
$profilePath = BASE_URL . 'graduate/profile.php';
$settingsPath = BASE_URL . 'graduate/profile.php';
$notificationCount = 0;
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'graduate':
            $logoLink = BASE_URL . 'graduate/dashboard.php';
            break;
        case 'employer':
            $logoLink = BASE_URL . 'employer/dashboard.php';
            $profilePath = BASE_URL . 'employer/profile.php';
            $settingsPath = BASE_URL . 'employer/profile.php';
            break;
        case 'admin':
            $logoLink = BASE_URL . 'admin/dashboard.php';
            $profilePath = BASE_URL . 'admin/dashboard.php';
            $settingsPath = BASE_URL . 'admin/dashboard.php';
            break;
    }
}

if (isset($_SESSION['user_id']) && isset($conn)) {
    $countStmt = $conn->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    if ($countStmt !== false) {
        $countStmt->bind_param('i', $_SESSION['user_id']);
        $countStmt->execute();
        $countStmt->bind_result($notificationCount);
        $countStmt->fetch();
        $countStmt->close();
    }
}
?>
<nav class="dashboard-topbar py-3">
    <div class="container-fluid">
        <div class="row align-items-center gx-3">
            <div class="col-12 col-md-4 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <a class="topbar-brand d-flex align-items-center gap-2" href="<?php echo $logoLink; ?>">
                    <span class="brand-mark"><i class="fas fa-briefcase"></i></span>
                    <span>
                        <span class="fw-bold">GradConnect SL</span>
                    </span>
                </a>
                <div>
                    <h1 class="topbar-title mb-0"><?php echo htmlspecialchars($pageTitle); ?></h1>
                </div>
            </div>
            <div class="col-12 col-md-8">
                <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-3">
                    <form class="dashboard-search-form d-flex" role="search">
                        <input class="form-control me-2" type="search" placeholder="Search jobs, companies..." aria-label="Search">
                        <button class="btn btn-primary-custom" type="submit">Search</button>
                    </form>
                    <div class="d-flex align-items-center gap-2 position-relative">
                        <a href="<?php echo BASE_URL; ?>notifications/index.php" class="btn btn-icon btn-light border position-relative" aria-label="Notifications">
                            <i class="fas fa-bell"></i>
                            <span id="notification-count-badge" class="badge bg-primary rounded-pill position-absolute top-0 start-100 translate-middle"><?php echo (int) $notificationCount; ?></span>
                        </a>
                        <span class="d-none d-md-inline text-muted"> Notifications</span>
                    </div>
                    <div class="dropdown">
                        <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if ($avatarUrl): ?>
                                <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Avatar" class="avatar-sm rounded-circle">
                            <?php else: ?>
                                <div class="avatar-placeholder rounded-circle">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                            <span class="ms-2 d-none d-md-inline text-dark fw-semibold"><?php echo htmlspecialchars($loggedInName); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuButton">
                            <li><a class="dropdown-item" href="<?php echo $profilePath; ?>">My Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo $settingsPath; ?>">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item logout-confirm" href="<?php echo BASE_URL; ?>auth/logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
