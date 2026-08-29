<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

$genericMessage = 'If the account exists and is not yet verified, a verification email has been sent.';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare(
            'SELECT user_id, full_name, role, email_verified, email_verification_sent_at
             FROM users
             WHERE email = ? AND role IN (\'graduate\', \'employer\')
             LIMIT 1'
        );

        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            $lastSentAt = !empty($user['email_verification_sent_at']) ? strtotime($user['email_verification_sent_at']) : 0;
            if ($user && (int) $user['email_verified'] !== 1 && ($lastSentAt === 0 || $lastSentAt <= time() - 60)) {
                $verificationToken = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $verificationToken);
                $expires = date('Y-m-d H:i:s', time() + 86400);
                $updateStmt = $conn->prepare(
                    'UPDATE users
                     SET email_verification_token = ?, email_verification_expires = ?, email_verification_sent_at = NOW()
                     WHERE user_id = ? AND email_verified = 0'
                );

                if ($updateStmt) {
                    $updateStmt->bind_param('ssi', $tokenHash, $expires, $user['user_id']);
                    $updateStmt->execute();
                    $updateStmt->close();
                    sendApplicationEmail($email, $user['full_name'], 'email_verification', [
                        'account_type' => $user['role'] . ' account',
                        'action_url' => APP_URL . '/auth/verify_email.php?token=' . urlencode($verificationToken),
                    ]);
                }
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $messageType = 'danger';
    $genericMessage = 'Invalid request.';
}
?>
<?php include '../includes/header.php'; ?>
<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="card-ui p-4 p-lg-5 bg-white">
                    <h1 class="h3 fw-bold text-center mb-3">Resend Verification Email</h1>
                    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                        <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                            <?php echo htmlspecialchars($genericMessage, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                    <form method="post" action="resend_verification.php">
                        <label for="email" class="form-label fw-semibold">Email Address</label>
                        <input type="email" class="form-control mb-3" id="email" name="email" required autocomplete="email">
                        <button type="submit" class="btn btn-primary-custom w-100">Send Verification Email</button>
                    </form>
                    <p class="text-center mt-3 mb-0"><a href="login.php">Go to Login</a></p>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>