<?php
/**
 * Authenticated dashboard topbar.
 */

if (!isset($pageTitle)) {
    $pageTitle = ' Dashboard';
}

$loggedInName = $_SESSION['full_name'] ?? 'User';
$avatarUrl = isset($dashboardAvatar) && $dashboardAvatar ? BASE_URL . 'uploads/profile_photos/' . $dashboardAvatar : null;

$logoLink = BASE_URL;
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'graduate':
            $logoLink = BASE_URL . 'graduate/dashboard.php';
            break;
        case 'employer':
            $logoLink = BASE_URL . 'employer/dashboard.php';
            break;
        case 'admin':
            $logoLink = BASE_URL . 'admin/dashboard.php';
            break;
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
                <h1 class="topbar-title mb-0"><?php echo htmlspecialchars($pageTitle); ?></h1>
            </div>
            <div class="col-12 col-md-8">
                <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-3">
                    <form class="dashboard-search-form d-flex" role="search">
                        <input class="form-control me-2" type="search" placeholder="Search jobs, companies..." aria-label="Search">
                        <button class="btn btn-primary-custom" type="submit">Search</button>
                    </form>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-icon btn-light border" type="button" aria-label="Notifications">
                            <i class="fas fa-bell"></i>
                        </button>
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
                            <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item logout-confirm" href="../auth/logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
