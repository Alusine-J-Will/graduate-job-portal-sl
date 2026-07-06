<?php
require_once '../config/config.php';

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

                    <!-- Progress Indicator -->
                    <div class="progress-steps mb-4">
                        <div class="step-pill completed">
                            <span class="step-icon"><i class="fas fa-check"></i></span>
                            <span>Account Info</span>
                        </div>
                        <div class="step-pill active">
                            <span class="step-icon"><i class="fas fa-building"></i></span>
                            <span>Company Details</span>
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

                    <form id="registration-form" class="mt-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="fullName" class="form-label fw-semibold">Full name</label>
                                <input type="text" class="form-control" id="fullName" placeholder="Enter your full name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="role" class="form-label fw-semibold">Account type</label>
                                <select class="form-select" id="role">
                                    <option selected>Graduate</option>
                                    <option>Employer</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">Email address</label>
                                <input type="email" class="form-control" id="email" placeholder="name@example.com" required>
                                <div id="email-status" class="email-status mt-2"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold">Phone number</label>
                                <input type="tel" class="form-control" id="phone" placeholder="+232 76 123 456">
                            </div>
                            <div class="col-12">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <input type="password" class="form-control" id="password" placeholder="Create a strong password" required>
                                <div class="strength-meter">
                                    <div id="password-strength-fill" class="strength-fill"></div>
                                </div>
                                <div id="password-strength-label" class="small mt-2 text-muted">Strength: Enter a password</div>
                                <ul class="password-requirements">
                                    <li class="requirement-item" data-requirement="length"><span class="requirement-icon">✖</span> At least 8 characters</li>
                                    <li class="requirement-item" data-requirement="uppercase"><span class="requirement-icon">✖</span> One uppercase letter</li>
                                    <li class="requirement-item" data-requirement="lowercase"><span class="requirement-icon">✖</span> One lowercase letter</li>
                                    <li class="requirement-item" data-requirement="number"><span class="requirement-icon">✖</span> One number</li>
                                    <li class="requirement-item" data-requirement="special"><span class="requirement-icon">✖</span> One special character</li>
                                </ul>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label text-muted" for="terms">
                                        I agree to the terms and conditions of GradConnect SL.
                                    </label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary-custom w-100 mt-4">Create Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
