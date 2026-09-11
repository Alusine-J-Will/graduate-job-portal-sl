<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

include '../includes/header.php';
include '../includes/navbar.php';
?>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card-ui p-4 p-lg-5 bg-white">
                    <div class="text-center mb-4">
                        <span class="badge hero-badge rounded-pill px-3 py-2">Graduate Registration</span>
                        <h1 class="h3 fw-bold mt-3 mb-2">Create your graduate account</h1>
                        <p class="text-muted mb-0">Join GradConnect SL to discover opportunities and connect with employers confidently.</p>
                    </div>

                    <?php if (isset($_SESSION['error'])) {
                         echo displayError($_SESSION['error']);
                        unset($_SESSION['error']);
                        }

                    if (isset($_SESSION['success'])) {
                        echo displaySuccess($_SESSION['success']);
                        unset($_SESSION['success']);
                    }?>

                    <form id="graduate-register-form" method="post" action="register_process.php" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label fw-semibold">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter first name" required>
                                <div class="invalid-feedback">Please enter your first name.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label fw-semibold">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter last name" required>
                                <div class="invalid-feedback">Please enter your last name.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                                <div id="email-status" class="email-status mt-2"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="+232 76 123 456" required>
                                <div class="invalid-feedback">Please enter a valid phone number.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Create a strong password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password" aria-label="Toggle password visibility">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Please provide a strong password.</div>
                                <div class="strength-meter mt-2">
                                    <div id="password-strength-fill" class="strength-fill"></div>
                                </div>
                                <div id="password-strength-label" class="small mt-2 text-muted">Strength: Enter a password</div>
                                <ul class="password-requirements mt-2">
                                    <li class="requirement-item" data-requirement="length"><span class="requirement-icon">✖</span> Minimum 8 characters</li>
                                    <li class="requirement-item" data-requirement="uppercase"><span class="requirement-icon">✖</span> Uppercase letter</li>
                                    <li class="requirement-item" data-requirement="lowercase"><span class="requirement-icon">✖</span> Lowercase letter</li>
                                    <li class="requirement-item" data-requirement="number"><span class="requirement-icon">✖</span> Number</li>
                                    <li class="requirement-item" data-requirement="special"><span class="requirement-icon">✖</span> Special character</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm_password" aria-label="Toggle password visibility">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Passwords do not match.</div>
                            </div>
                                <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label text-muted" for="terms">
                                        I have read and agree to the <button type="button" class="btn btn-link p-0 align-baseline text-decoration-none" data-bs-toggle="modal" data-bs-target="#termsModal">GradConnect SL Terms &amp; Conditions</button> and <button type="button" class="btn btn-link p-0 align-baseline text-decoration-none" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Notice</button>.
                                    </label>
                                    <div class="invalid-feedback">You must accept the terms to continue.</div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100 mt-4">Register</button>

                        <p class="text-center mt-3 mb-0 text-muted">
                            Already have an account? <a href="login.php" class="text-decoration-none fw-semibold">Login</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/legal_modals.php'; ?>
<script src="<?php echo BASE_URL; ?>assets/js/register.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof initLegalModals === 'function') {
            initLegalModals('graduate');
        }
    });
</script>
<?php include '../includes/footer.php'; ?>
