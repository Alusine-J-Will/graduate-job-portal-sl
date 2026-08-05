<?php
/**
 * Admin sidebar navigation for GradConnect SL.
 */

$currentPage = basename($_SERVER['PHP_SELF']);
function isActiveAdminPage(string $page): string
{
    $currentPath = str_replace('\\', '/', ltrim($_SERVER['SCRIPT_NAME'], '/'));
    $pagePath = str_replace('\\', '/', ltrim($page, '/'));

    if (strpos($pagePath, '/') !== false) {
        if ($pagePath === $currentPath || substr($currentPath, -strlen($pagePath)) === $pagePath) {
            return 'active';
        }
        return '';
    }

    return basename($currentPath) === $pagePath ? 'active' : '';
}
?>
<nav class="sidebar sidebar-admin">
    <div class="sidebar-brand">
        <span class="brand-mark"><i class="fas fa-shield-alt"></i></span>
        <h5 class="sidebar-title mb-0">Admin Panel</h5>
    </div>
    <ul class="nav flex-column gap-1">
        <li class="nav-item">
            <a class="nav-link <?php echo isActiveAdminPage('dashboard.php'); ?>" href="<?php echo BASE_URL; ?>admin/dashboard.php"><i class="fas fa-th-large me-2"></i>Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActiveAdminPage('graduates.php'); ?>" href="<?php echo BASE_URL; ?>admin/graduates.php"><i class="fas fa-user-graduate me-2"></i>Manage Graduates</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActiveAdminPage('employers.php'); ?>" href="<?php echo BASE_URL; ?>admin/employers.php"><i class="fas fa-building me-2"></i>Manage Employers</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActiveAdminPage('jobs.php'); ?>" href="<?php echo BASE_URL; ?>admin/jobs.php"><i class="fas fa-briefcase me-2"></i>Manage Jobs</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActiveAdminPage('applications.php'); ?>" href="<?php echo BASE_URL; ?>admin/applications.php"><i class="fas fa-file-alt me-2"></i>Manage Applications</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActiveAdminPage('reports.php'); ?>" href="<?php echo BASE_URL; ?>admin/reports.php"><i class="fas fa-chart-line me-2"></i>Reports & Analytics</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isActiveAdminPage('notifications/index.php'); ?>" href="<?php echo BASE_URL; ?>notifications/index.php"><i class="fas fa-bell me-2"></i>Notifications</a>
        </li>
        <li class="nav-item mt-3">
            <a class="nav-link text-danger fw-semibold logout-confirm" href="<?php echo BASE_URL; ?>auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
        </li>
    </ul>
</nav>
