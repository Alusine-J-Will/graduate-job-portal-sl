<?php
/**
 * Shared navigation bar partial.
 *
 * Displays the main site navigation.
 */
?>
<nav class="navbar navbar-expand-lg navbar-light navbar-main shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-3" href="<?php echo BASE_URL; ?>">
            <span class="brand-mark"><i class="fas fa-briefcase"></i></span>
            <span>
                <span class="fw-bold text-dark d-block">GradConnect SL</span>
                <small class="text-muted d-block">Connecting Graduates to Opportunities</small>
            </span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>#featured-jobs">Jobs</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>#about">About</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>#partners">Companies</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>#contact">Contact</a></li>
                <li class="nav-item ms-lg-3"><a class="nav-link nav-link-secondary" href="<?php echo BASE_URL; ?>auth/login.php">Login</a></li>
                <li class="nav-item ms-lg-2"><a class="btn btn-primary btn-sm rounded-pill px-4 py-2" href="<?php echo BASE_URL; ?>auth/register.php">Register</a></li>
            </ul>
        </div>
    </div>
</nav>
