<?php
/**
 * Reusable dashboard sidebar menu.
 */

$currentPage = basename($_SERVER['PHP_SELF']);
function isActivePage(string $page): string
{
    global $currentPage;
    return $currentPage === $page ? 'active' : '';
}
?>
<nav class="sidebar card-ui bg-white p-3">
    <div class="sidebar-header mb-4">
        <h5 class="fw-semibold mb-1">Graduate Portal</h5>
        <p class="small text-muted mb-0">Career Dashboard</p>
    </div>
    <ul class="nav flex-column gap-1">
        <li class="nav-item">
            <a class="nav-link <?php echo isActivePage('dashboard.php'); ?>" href="dashboard.php"><i class="fas fa-th-large me-2"></i>Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActivePage('profile.php'); ?>" href="profile.php"><i class="fas fa-user me-2"></i>My Profile</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActivePage('jobs.php'); ?>" href="jobs.php"><i class="fas fa-search me-2"></i>Search Jobs</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActivePage('applications.php'); ?>" href="applications.php"><i class="fas fa-file-alt me-2"></i>Applications</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActivePage('saved_jobs.php'); ?>" href="saved_jobs.php"><i class="fas fa-bookmark me-2"></i>Saved Jobs</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActivePage('notifications.php'); ?>" href="notifications.php"><i class="fas fa-bell me-2"></i>Notifications</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActivePage('settings.php'); ?>" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a>
        </li>
        <li class="nav-item mt-3">
            <a class="nav-link text-danger fw-semibold logout-confirm" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
        </li>
    </ul>
</nav>
