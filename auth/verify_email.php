<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

$message = 'This verification link is invalid or has expired.';
$messageType = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['token'])) {
    $token = (string) $_GET['token'];

    if (preg_match('/^[a-f0-9]{64}$/', $token)) {
        $tokenHash = hash('sha256', $token);
        $stmt = $conn->prepare(
            'SELECT user_id, email_verified, email_verification_expires
             FROM users
             WHERE email_verification_token = ?
             LIMIT 1'
        );

        if ($stmt) {
            $stmt->bind_param('s', $tokenHash);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user && (int) $user['email_verified'] === 1) {
                $message = 'This email address has already been verified. You can log in.';
                $messageType = 'success';
            } elseif ($user && !empty($user['email_verification_expires']) && strtotime($user['email_verification_expires']) >= time()) {
                $updateStmt = $conn->prepare(
                    'UPDATE users
                     SET email_verified = 1,
                         email_verification_token = NULL,
                         email_verification_expires = NULL
                     WHERE user_id = ? AND email_verification_token = ? AND email_verified = 0'
                );

                if ($updateStmt) {
                    $updateStmt->bind_param('is', $user['user_id'], $tokenHash);
                    $updateStmt->execute();
                    $verified = $updateStmt->affected_rows === 1;
                    $updateStmt->close();

                    if ($verified) {
                        $message = 'Email verified successfully. You can now log in.';
                        $messageType = 'success';
                    } else {
                        $message = 'This verification link has already been used.';
                    }
                }
            } elseif ($user) {
                $message = 'This verification link has expired. Please request a new verification email.';
            }
        }
    }
}
?>
<?php include '../includes/header.php'; ?>
<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card-ui p-4 p-lg-5 bg-white text-center">
                    <h1 class="h3 fw-bold mb-3">Email Verification</h1>
                    <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                        <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <a class="btn btn-primary-custom" href="login.php">Go to Login</a>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>