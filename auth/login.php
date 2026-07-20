<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

include '../includes/header.php';
include '../includes/navbar.php';
?>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="card-ui p-4 p-lg-5 bg-white">
                    <div class="text-center mb-4">
                        <span class="badge hero-badge rounded-pill px-3 py-2">Welcome Back</span>
                        <h1 class="h3 fw-bold mt-3 mb-2">Sign in to your account</h1>
                        <p class="text-muted mb-0">Access jobs, employers, and career opportunities.</p>
                    </div>

                    <?php
                        if (isset($_SESSION['success'])) {
                        echo displaySuccess($_SESSION['success']);
                        unset($_SESSION['success']);
                    }

                        if (isset($_SESSION['warning'])) {
                        echo displayWarning($_SESSION['warning']);
                        unset($_SESSION['warning']);
                    }

                    if (isset($_SESSION['error'])) {
                        echo displayError($_SESSION['error']);
                        unset($_SESSION['error']);
                    }
                    ?>

                    <form id="login-form" method="post" action="login_process.php" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" autocomplete="email" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">Please enter your password.</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
                                <label class="form-check-label text-muted" for="remember_me">Remember me</label>
                            </div>
                            <a href="forgot_password.php" class="small text-decoration-none">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100">Login</button>

                        <p class="text-center mt-3 mb-0 text-muted">
                            Don't have an account? <a href="graduate_register.php" class="text-decoration-none fw-semibold">Register</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="<?php echo BASE_URL; ?>assets/js/login.js"></script>
<?php include '../includes/footer.php'; ?>
