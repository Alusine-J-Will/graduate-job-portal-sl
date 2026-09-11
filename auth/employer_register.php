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
                        <span class="badge hero-badge rounded-pill px-3 py-2">Employer Registration</span>
                        <h1 class="h3 fw-bold mt-3 mb-2">Create your employer account</h1>
                        <p class="text-muted mb-0">Register your company and start posting job opportunities for graduates.</p>
                    </div>

                    <?php
                    if (isset($_SESSION['error'])) {
                        echo displayError($_SESSION['error']);
                        unset($_SESSION['error']);
                    }

                    if (isset($_SESSION['success'])) {
                        echo displaySuccess($_SESSION['success']);
                        unset($_SESSION['success']);
                    }
                    ?>

                    <form id="employer-register-form" method="post" action="employer_register_process.php" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="full_name" class="form-label fw-semibold">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Enter your full name" required>
                                <div class="invalid-feedback">Please enter your full name.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="company_name" class="form-label fw-semibold">Company Name</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Enter your company name" required>
                                <div class="invalid-feedback">Please enter your company name.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">Business Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="company@example.com" autocomplete="email" required>
                                <div class="invalid-feedback">Please enter a valid business email.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="+232 76 123 456" required>
                                <div class="invalid-feedback">Please enter a valid phone number.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="industry" class="form-label fw-semibold">Industry</label>
                                <input type="text" class="form-control" id="industry" name="industry" placeholder="Enter your industry" required>
                                <div class="invalid-feedback">Please enter your industry.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="company_size" class="form-label fw-semibold">Company Size</label>
                                <input type="text" class="form-control" id="company_size" name="company_size" placeholder="e.g. 10-50 employees" required>
                                <div class="invalid-feedback">Please enter your company size.</div>
                            </div>
                            <div class="col-md-12">
                                <label for="website" class="form-label fw-semibold">Website <small class="text-muted">(optional)</small></label>
                                <input type="url" class="form-control" id="website" name="website" placeholder="https://www.example.com">
                                <div class="invalid-feedback">Please enter a valid website URL.</div>
                            </div>
                            <div class="col-12">
                                <label for="company_address" class="form-label fw-semibold">Company Address</label>
                                <textarea class="form-control" id="company_address" name="company_address" rows="3" placeholder="Enter your company address" required></textarea>
                                <div class="invalid-feedback">Please enter your company address.</div>
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
            initLegalModals('employer');
        }
    });
</script>
<?php include '../includes/footer.php'; ?>
