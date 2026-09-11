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
                        <span class="badge hero-badge rounded-pill px-3 py-2">Join GradConnect SL</span>
                        <h1 class="h3 fw-bold mt-3 mb-2">Create your account</h1>
                        <p class="text-muted mb-0">Register to explore graduate opportunities and connect with employers confidently.</p>
                    </div>

                    <?php displayFlashMessages(); ?>

                    <!-- Progress Indicator -->
                    <div class="progress-steps mb-4">
                        <div class="step-pill completed">
                            <span class="step-icon"><i class="fas fa-check"></i></span>
                            <span>Account Info</span>
                        </div>
                        <div class="step-pill active">
                            <span class="step-icon"><i class="fas fa-building"></i></span>
                            <span>Account Setup</span>
                        </div>
                        <div class="step-pill">
                            <span class="step-icon"><i class="fas fa-shield-alt"></i></span>
                            <span>Verification</span>
                        </div>
                        <div class="step-pill">
                            <span class="step-icon"><i class="fas fa-flag-checkered"></i></span>
                            <span>Completion</span>
                        </div>
                    </div>

                    <form id="registration-form" class="mt-3" method="post" action="register_process.php" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="role" class="form-label fw-semibold">Account type</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="graduate" selected>Graduate</option>
                                    <option value="employer">Employer</option>
                                </select>
                                <div class="invalid-feedback">Please select an account type.</div>
                            </div>
                        </div>

                        <div id="graduate-fields" class="mt-4">
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
                            </div>
                        </div>

                        <div id="employer-fields" class="mt-4 d-none">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="full_name" class="form-label fw-semibold">Contact Person</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Enter contact person name">
                                    <div class="invalid-feedback">Please enter the contact person name.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="company_name" class="form-label fw-semibold">Company Name</label>
                                    <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Enter your company name">
                                    <div class="invalid-feedback">Please enter your company name.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="industry" class="form-label fw-semibold">Industry</label>
                                    <input type="text" class="form-control" id="industry" name="industry" placeholder="Enter your industry">
                                    <div class="invalid-feedback">Please enter your industry.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="company_size" class="form-label fw-semibold">Company Size</label>
                                    <input type="text" class="form-control" id="company_size" name="company_size" placeholder="e.g. 10-50 employees">
                                    <div class="invalid-feedback">Please enter your company size.</div>
                                </div>
                                <div class="col-12">
                                    <label for="company_address" class="form-label fw-semibold">Company Address</label>
                                    <textarea class="form-control" id="company_address" name="company_address" rows="3" placeholder="Enter your company address"></textarea>
                                    <div class="invalid-feedback">Please enter your company address.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-4">
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
                        </div>

                        <div class="row g-3 mt-4">
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Create a strong password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password" aria-label="Toggle password visibility">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <div class="invalid-feedback">Please provide a strong password.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm_password" aria-label="Toggle password visibility">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <div class="invalid-feedback">Passwords do not match.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="strength-meter">
                                <div class="strength-fill"></div>
                            </div>
                            <div id="password-strength-label-bottom" class="small mt-2 text-muted">Strength: Enter a password</div>
                        </div>

                        <ul class="password-requirements mt-3">
                            <li class="requirement-item" data-requirement="length"><span class="requirement-icon">✖</span> At least 8 characters</li>
                            <li class="requirement-item" data-requirement="uppercase"><span class="requirement-icon">✖</span> One uppercase letter</li>
                            <li class="requirement-item" data-requirement="lowercase"><span class="requirement-icon">✖</span> One lowercase letter</li>
                            <li class="requirement-item" data-requirement="number"><span class="requirement-icon">✖</span> One number</li>
                            <li class="requirement-item" data-requirement="special"><span class="requirement-icon">✖</span> One special character</li>
                        </ul>

                        <div class="col-12 mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label text-muted" for="terms">
                                    I have read and agree to the <button type="button" class="btn btn-link p-0 align-baseline text-decoration-none" data-bs-toggle="modal" data-bs-target="#termsModal">GradConnect SL Terms &amp; Conditions</button> and <button type="button" class="btn btn-link p-0 align-baseline text-decoration-none" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Notice</button>.
                                </label>
                                <div class="invalid-feedback">You must accept the terms to continue.</div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100 mt-4">Create Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/legal_modals.php'; ?>
<script src="<?php echo BASE_URL; ?>assets/js/register.js"></script>
<?php include '../includes/footer.php'; ?>
