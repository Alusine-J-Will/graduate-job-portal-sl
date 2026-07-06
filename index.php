<?php
require_once 'config/config.php';

$stats = [
    ['icon' => 'fas fa-briefcase', 'value' => '150+', 'label' => 'Jobs Available'],
    ['icon' => 'fas fa-user-graduate', 'value' => '1200+', 'label' => 'Graduates'],
    ['icon' => 'fas fa-building', 'value' => '80+', 'label' => 'Employers'],
    ['icon' => 'fas fa-award', 'value' => '300+', 'label' => 'Successful Placements'],
];

$featuredJobs = [
    [
        'title' => 'Junior Web Developer',
        'company' => 'Sierra Digital Labs',
        'location' => 'Freetown',
        'type' => 'Full-Time',
        'deadline' => '25 Jul 2026',
        'description' => 'Support the development team with modern web interfaces and responsive front-end features.',
    ],
    [
        'title' => 'Data Entry Officer',
        'company' => 'National Savings Bank',
        'location' => 'Bo',
        'type' => 'Contract',
        'deadline' => '15 Aug 2026',
        'description' => 'Manage records, update digital forms, and assist with administrative reporting tasks.',
    ],
    [
        'title' => 'Customer Support Associate',
        'company' => 'EcoTech Sierra Leone',
        'location' => 'Makeni',
        'type' => 'Part-Time',
        'deadline' => '10 Aug 2026',
        'description' => 'Deliver excellent client support and coordinate service requests across digital channels.',
    ],
    [
        'title' => 'Graduate Accounts Assistant',
        'company' => 'Blue Horizon Finance',
        'location' => 'Kenema',
        'type' => 'Full-Time',
        'deadline' => '20 Aug 2026',
        'description' => 'Assist with bookkeeping, reconciliations, and daily financial documentation.',
    ],
    [
        'title' => 'Software Support Intern',
        'company' => 'Innovate Sierra',
        'location' => 'Freetown',
        'type' => 'Internship',
        'deadline' => '30 Jul 2026',
        'description' => 'Gain practical experience supporting software deployment and user troubleshooting.',
    ],
    [
        'title' => 'Marketing Assistant',
        'company' => 'BrightLink Agency',
        'location' => 'Port Loko',
        'type' => 'Full-Time',
        'deadline' => '05 Sep 2026',
        'description' => 'Help execute campaigns, manage content, and engage with diverse audiences online.',
    ],
];

$features = [
    ['icon' => 'fas fa-user-check', 'title' => 'Graduate Focused', 'description' => 'Opportunities tailored to the needs and skills of new graduates.'],
    ['icon' => 'fas fa-shield-alt', 'title' => 'Verified Employers', 'description' => 'Work with trusted organizations and genuine career opportunities.'],
    ['icon' => 'fas fa-paper-plane', 'title' => 'Simple Applications', 'description' => 'Apply in a few clicks with a streamlined and stress-free experience.'],
    ['icon' => 'fas fa-mobile-alt', 'title' => 'Mobile Friendly', 'description' => 'Access the portal easily from your phone, tablet, or desktop.'],
];

$steps = [
    ['icon' => 'fas fa-user-plus', 'title' => 'Create Account'],
    ['icon' => 'fas fa-id-card', 'title' => 'Complete Profile'],
    ['icon' => 'fas fa-search', 'title' => 'Apply for Jobs'],
    ['icon' => 'fas fa-trophy', 'title' => 'Get Hired'],
];

$partners = ['Sierra Digital', 'Blue Horizon', 'EcoTech', 'Nova Bank', 'BrightLink', 'Crown Labs'];

$testimonials = [
    [
        'name' => 'Aminata Kamara',
        'role' => 'Computer Science Graduate',
        'university' => 'Fourah Bay College',
        'quote' => 'I found my first role within two weeks of creating my profile. The process was simple and professional.',
    ],
    [
        'name' => 'Joseph Bangura',
        'role' => 'Business Information Graduate',
        'university' => 'Njala University',
        'quote' => 'The platform made it easy to connect with employers who truly value fresh graduates.',
    ],
    [
        'name' => 'Mariama Conteh',
        'role' => 'Software Engineering Graduate',
        'university' => 'IPAM',
        'quote' => 'I appreciated the clear job recommendations and the polished application experience.',
    ],
];

include 'includes/header.php';
include 'includes/navbar.php';
?>

<main>
    <!-- Hero Section -->
    <section class="hero-section py-5 py-lg-6">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <span class="badge hero-badge rounded-pill px-3 py-2 mb-3">Trusted by Graduates Across Sierra Leone</span>
                    <h1 class="display-5 fw-bold mb-3 text-dark">Launch Your Career with Sierra Leone's Graduate Job Portal</h1>
                    <p class="lead text-muted mb-4">Discover verified graduate opportunities, connect with trusted employers, and begin your professional journey—all in one place.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="#featured-jobs" class="btn btn-primary btn-lg px-4 rounded-pill">Browse Jobs</a>
                        <a href="<?php echo BASE_URL; ?>auth/register.php" class="btn btn-outline-primary btn-lg px-4 rounded-pill">Create Account</a>
                    </div>
                    <div class="d-flex flex-wrap gap-3 mt-4 text-muted small">
                        <span><i class="fas fa-check-circle text-primary me-2"></i>Verified employers</span>
                        <span><i class="fas fa-check-circle text-primary me-2"></i>Fast applications</span>
                        <span><i class="fas fa-check-circle text-primary me-2"></i>Graduate-first focus</span>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card hero-card border-0 shadow-sm rounded-4 p-4 p-lg-5">
                        <div class="text-center">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 p-4 mb-4">
                                <i class="fas fa-rocket fa-4x text-primary"></i>
                            </div>
                            <h3 class="h5 fw-semibold mb-2">Your next opportunity starts here</h3>
                            <p class="text-muted mb-0">Explore internships, entry-level roles, and professional opportunities tailored for ambitious graduates.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <?php foreach ($stats as $stat): ?>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body text-center p-4">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 p-3 mb-3">
                                    <i class="<?php echo $stat['icon']; ?> fa-2x text-primary"></i>
                                </div>
                                <h3 class="h2 fw-bold mb-1 text-dark"><?php echo $stat['value']; ?></h3>
                                <p class="text-muted mb-0"><?php echo $stat['label']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Jobs Section -->
    <section id="featured-jobs" class="py-5 bg-light">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-2">Featured Jobs</h2>
                    <p class="text-muted mb-0">Recent opportunities suitable for ambitious graduates across Sierra Leone.</p>
                </div>
                <a href="#" class="btn btn-outline-primary">View All Jobs</a>
            </div>
            <div class="row g-4">
                <?php foreach ($featuredJobs as $job): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                    <div>
                                        <h3 class="h5 fw-semibold mb-1"><?php echo htmlspecialchars($job['title']); ?></h3>
                                        <p class="text-primary mb-0"><?php echo htmlspecialchars($job['company']); ?></p>
                                    </div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill"><?php echo htmlspecialchars($job['type']); ?></span>
                                </div>
                                <p class="text-muted small mb-3"><?php echo htmlspecialchars($job['description']); ?></p>
                                <ul class="list-unstyled text-muted small mb-4">
                                    <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($job['location']); ?></li>
                                    <li><i class="fas fa-calendar-alt me-2"></i>Deadline: <?php echo htmlspecialchars($job['deadline']); ?></li>
                                </ul>
                                <div class="d-flex gap-2">
                                    <a href="#" class="btn btn-outline-primary btn-sm">View Details</a>
                                    <a href="#" class="btn btn-primary btn-sm">Apply</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Why Choose Our Platform Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold mb-2">Why Choose Our Platform</h2>
                <p class="text-muted mb-0">Everything you need to launch a strong career journey in one place.</p>
            </div>
            <div class="row g-4">
                <?php foreach ($features as $feature): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-4 text-center">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 p-3 mb-3">
                                    <i class="<?php echo $feature['icon']; ?> fa-2x text-primary"></i>
                                </div>
                                <h3 class="h5 fw-semibold mb-2"><?php echo htmlspecialchars($feature['title']); ?></h3>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($feature['description']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold mb-2">How It Works</h2>
                <p class="text-muted mb-0">A simple path from registration to your first professional opportunity.</p>
            </div>
            <div class="row g-4">
                <?php foreach ($steps as $index => $step): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="text-center">
                            <div class="d-flex justify-content-center align-items-center mb-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="<?php echo $step['icon']; ?> fa-2x"></i>
                                </div>
                            </div>
                            <h3 class="h6 fw-semibold mb-2"><?php echo htmlspecialchars($step['title']); ?></h3>
                            <p class="text-muted small mb-0">Step <?php echo $index + 1; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Partner Companies Section -->
    <section id="partners" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold mb-2">Partner Companies</h2>
                <p class="text-muted mb-0">Trusted organizations hiring graduates from across the country.</p>
            </div>
            <div class="row g-4">
                <?php foreach ($partners as $partner): ?>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                            <div class="bg-light rounded-3 py-4 px-3">
                                <span class="fw-semibold text-muted"><?php echo htmlspecialchars($partner); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold mb-2">Testimonials</h2>
                <p class="text-muted mb-0">What graduates are saying about their experience.</p>
            </div>
            <div class="row g-4">
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <span class="fw-semibold text-primary"><?php echo strtoupper(substr($testimonial['name'], 0, 2)); ?></span>
                                    </div>
                                    <div>
                                        <h3 class="h6 fw-semibold mb-1"><?php echo htmlspecialchars($testimonial['name']); ?></h3>
                                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($testimonial['role']); ?></p>
                                    </div>
                                </div>
                                <p class="text-muted mb-3">“<?php echo htmlspecialchars($testimonial['quote']); ?>”</p>
                                <p class="small fw-semibold text-primary mb-0"><?php echo htmlspecialchars($testimonial['university']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Call To Action Section -->
    <section id="cta" class="py-5">
        <div class="container">
            <div class="bg-primary text-white rounded-4 p-5 text-center shadow-sm">
                <h2 class="fw-bold mb-3">Ready to Start Your Career?</h2>
                <p class="lead mb-4">Create an account, build your profile, and discover the right opportunities for you.</p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="auth/register.php" class="btn btn-light text-primary fw-semibold">Register</a>
                    <a href="#featured-jobs" class="btn btn-outline-light">Browse Jobs</a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
