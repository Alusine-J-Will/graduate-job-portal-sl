<?php
require_once '../config/config.php';

$redirectUrl = BASE_URL . 'auth/login.php';
$cancelUrl = $_SERVER['HTTP_REFERER'] ?? BASE_URL . 'graduate/dashboard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['confirm_logout'])) {
    session_unset();
    session_destroy();

    session_start();
    $_SESSION['success'] = 'You have been logged out.';

    header('Location: ' . $redirectUrl);
    exit;
}

include '../includes/header.php';
?>
<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card-ui p-4 bg-white text-center">
                    <span class="badge hero-badge rounded-pill px-3 py-2">Confirm Logout</span>
                    <h1 class="h4 fw-bold mt-3 mb-2">Are you sure you want to logout?</h1>
                    <p class="text-muted mb-4">You will be redirected to the login page after signing out.</p>

                    <form method="post" action="logout.php">
                        <input type="hidden" name="confirm_logout" value="1">
                        <button type="submit" class="btn btn-danger btn-lg me-2">Logout</button>
                        <a href="<?php echo htmlspecialchars($cancelUrl); ?>" class="btn btn-secondary btn-lg">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include '../includes/footer.php';
