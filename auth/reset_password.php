<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

$message = '';
$messageType = 'danger';
$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$tokenValid = false;

if (preg_match('/^[a-f0-9]{64}$/', $token)) {
    $tokenHash = hash('sha256', $token);
    $stmt = $conn->prepare(
        'SELECT user_id, password_reset_expires
         FROM users
         WHERE password_reset_token = ?
         LIMIT 1'
    );
    if ($stmt) {
        $stmt->bind_param('s', $tokenHash);
        $stmt->execute();
        $result = $stmt->get_result();
        $resetUser = $result->fetch_assoc();
        $stmt->close();
        $tokenValid = $resetUser && !empty($resetUser['password_reset_expires']) && strtotime($resetUser['password_reset_expires']) >= time();
        if (!$tokenValid && $resetUser) {
            $message = 'This password reset link has expired. Please request a new password reset link.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must include an uppercase letter.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must include a lowercase letter.';
    }
    if (!preg_match('/\d/', $password)) {
        $errors[] = 'Password must include a number.';
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must include a special character.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare(
            'UPDATE users
             SET password = ?, password_reset_token = NULL, password_reset_expires = NULL, password_reset_sent_at = NULL
             WHERE user_id = ? AND password_reset_token = ? AND password_reset_expires >= NOW()'
        );
        if ($updateStmt) {
            $updateStmt->bind_param('sis', $hashedPassword, $resetUser['user_id'], $tokenHash);
            $updateStmt->execute();
            $updated = $updateStmt->affected_rows === 1;
            $updateStmt->close();
            if ($updated) {
                $_SESSION['success'] = 'Your password has been reset successfully. You can now log in with your new password.';
                redirect('login.php');
            }
        }
        $message = 'This password reset link is invalid or has already been used.';
    } else {
        $message = implode(' ', $errors);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$tokenValid && $message === '') {
    $message = 'This password reset link is invalid or has already been used.';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !$tokenValid && $message === '') {
    $message = 'This password reset link is invalid or has already been used.';
}
?>
<?php include '../includes/header.php'; ?>
<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="card-ui p-4 p-lg-5 bg-white">
                    <h1 class="h3 fw-bold text-center mb-3">Reset Password</h1>
                    <?php if ($message !== ''): ?>
                        <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($tokenValid): ?>
                        <form method="post" action="reset_password.php">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password">
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label fw-semibold">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                            </div>
                            <button type="submit" class="btn btn-primary-custom w-100">Reset Password</button>
                        </form>
                    <?php else: ?>
                        <a href="forgot_password.php" class="btn btn-primary-custom w-100">Request New Reset Link</a>
                    <?php endif; ?>
                    <p class="text-center mt-3 mb-0"><a href="login.php">Back to Login</a></p>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>