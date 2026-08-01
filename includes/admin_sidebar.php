<?php
/**
 * Admin sidebar navigation for GradConnect SL.
 */

$currentPage = basename($_SERVER['PHP_SELF']);
function isActiveAdminPage(string $page): string
{
    global $currentPage;
    return $currentPage === $page ? 'active' : '';
}
?>
<nav class="sidebar card-ui bg-white p-3">
    <div class="sidebar-header mb-4">
        <h5 class="fw-semibold mb-1">Admin Panel</h5>
        <p class="small text-muted mb-0">Administration Navigation</p>
    </div>
    <ul class="nav flex-column gap-1">
        <li class="nav-item">
            <a class="nav-link <?php echo isActiveAdminPage('dashboard.php'); ?>" href="dashboard.php"><i class="fas fa-th-large me-2"></i>Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActiveAdminPage('graduates.php'); ?>" href="graduates.php"><i class="fas fa-user-graduate me-2"></i>Manage Graduates</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActiveAdminPage('employers.php'); ?>" href="employers.php"><i class="fas fa-building me-2"></i>Manage Employers</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActiveAdminPage('jobs.php'); ?>" href="jobs.php"><i class="fas fa-briefcase me-2"></i>Manage Jobs</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActiveAdminPage('applications.php'); ?>" href="applications.php"><i class="fas fa-file-alt me-2"></i>Manage Applications</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActiveAdminPage('reports.php'); ?>" href="reports.php"><i class="fas fa-chart-line me-2"></i>Reports & Analytics</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActiveAdminPage('notifications.php'); ?>" href="notifications.php"><i class="fas fa-bell me-2"></i>Notifications</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActiveAdminPage('settings.php'); ?>" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a>
        </li>
        <li class="nav-item mt-3">
            <a class="nav-link text-danger fw-semibold logout-confirm" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
        </li>
    </ul>
</nav>
