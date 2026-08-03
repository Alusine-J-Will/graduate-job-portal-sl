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
<nav class="sidebar sidebar-portal">
    <div class="sidebar-brand">
        <span class="brand-mark">
            <i class="fas <?php echo isset($_SESSION['role']) && $_SESSION['role'] === 'employer' ? 'fa-building' : 'fa-user-graduate'; ?>"></i>
        </span>
        <div>
            <h5 class="sidebar-title mb-1"><?php echo isset($_SESSION['role']) && $_SESSION['role'] === 'employer' ? 'Employer Portal' : 'Graduate Portal'; ?></h5>
            <p class="sidebar-subtitle mb-0"><?php echo isset($_SESSION['role']) && $_SESSION['role'] === 'employer' ? 'Company Dashboard' : 'Career Dashboard'; ?></p>
        </div>
    </div>
    <ul class="nav flex-column gap-1">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'employer'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isActivePage('dashboard.php'); ?>" href="dashboard.php"><i class="fas fa-th-large me-2"></i>Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActivePage('profile.php'); ?>" href="profile.php"><i class="fas fa-building me-2"></i>Company Profile</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActivePage('post_job.php'); ?>" href="post_job.php"><i class="fas fa-plus-circle me-2"></i>Post a Job</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActivePage('applicants.php'); ?>" href="applicants.php"><i class="fas fa-users me-2"></i>Applicants</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActivePage('manage_jobs.php'); ?>" href="manage_jobs.php"><i class="fas fa-tasks me-2"></i>Manage Jobs</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActivePage('edit_profile.php'); ?>" href="edit_profile.php"><i class="fas fa-edit me-2"></i>Edit Profile</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActivePage('settings.php'); ?>" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a>
            </li>
        <?php else: ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isActivePage('dashboard.php'); ?>" href="dashboard.php"><i class="fas fa-th-large me-2"></i>Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActivePage('profile.php'); ?>" href="profile.php"><i class="fas fa-user me-2"></i>My Profile</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActivePage('education.php'); ?>" href="education.php"><i class="fas fa-graduation-cap me-2"></i>Education</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActivePage('experience.php'); ?>" href="experience.php"><i class="fas fa-briefcase me-2"></i>Experience</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActivePage('skills.php'); ?>" href="skills.php"><i class="fas fa-tags me-2"></i>Skills</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActivePage('jobs.php'); ?>" href="jobs.php"><i class="fas fa-search me-2"></i>Search Jobs</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActivePage('my_applications.php'); ?>" href="my_applications.php"><i class="fas fa-file-alt me-2"></i>Applications</a>
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
        <?php endif; ?>
        <li class="nav-item mt-3">
            <a class="nav-link text-danger fw-semibold logout-confirm" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
        </li>
    </ul>
</nav>
