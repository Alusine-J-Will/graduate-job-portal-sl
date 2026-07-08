<?php
/**
 * Shared HTML footer partial.
 *
 * Closes the page layout and loads JavaScript assets.
 */
?>
<footer id="contact" class="site-footer mt-auto">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-4">
                <a class="navbar-brand d-flex align-items-center gap-2 mb-3" href="<?php echo BASE_URL; ?>">
                    <span class="brand-mark"><i class="fas fa-briefcase"></i></span>
                    <span>
                        <span class="fw-bold d-block">GradConnect SL</span>
                        <small class="text-muted d-block">Connecting Graduates</small>
                    </span>
                </a>
                <p class="text-muted mb-0">A modern job portal designed to help graduates in Sierra Leone discover opportunities and connect with employers confidently.</p>
            </div>
            <div class="col-sm-6 col-lg-2">
                <h5 class="fw-semibold mb-3">Quick Links</h5>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>#featured-jobs">Jobs</a></li>
                    <li><a href="<?php echo BASE_URL; ?>#partners">Companies</a></li>
                    <li><a href="<?php echo BASE_URL; ?>auth/register.php">Register</a></li>
                </ul>
            </div>
            <div class="col-sm-6 col-lg-3">
                <h5 class="fw-semibold mb-3">Contact</h5>
                <ul class="list-unstyled footer-links">
                    <li><i class="fas fa-envelope me-2"></i>info@gradconnectsl.com</li>
                    <li><i class="fas fa-phone me-2"></i>+232 75 947 842</li>
                    <li><i class="fas fa-map-marker-alt me-2"></i>Freetown, Sierra Leone</li>
                </ul>
            </div>
            <div class="col-lg-3">
                <h5 class="fw-semibold mb-3">Follow Us</h5>
                <div class="d-flex gap-2">
                    <a href="#" class="social-link" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-link" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom mt-4 pt-3 border-top text-center text-muted">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> GradConnect SL. All rights reserved.</p>
        </div>
    </div>
</footer>
<script src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/graduate_register.js"></script>
</body>
</html>
