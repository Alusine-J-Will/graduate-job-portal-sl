<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

$genericMessage = 'If an account exists for that email address, a password reset link has been sent.';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request.';
    redirect('forgot_password.php');
}

$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please enter a valid email address.';
    redirect('forgot_password.php');
}

$conn = $GLOBALS['conn'];
$stmt = $conn->prepare(
    'SELECT user_id, full_name, role, password_reset_sent_at
     FROM users
     WHERE email = ?
     LIMIT 1'
);

if ($stmt) {
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    $lastSentAt = !empty($user['password_reset_sent_at']) ? strtotime($user['password_reset_sent_at']) : 0;
    if ($user && ($lastSentAt === 0 || $lastSentAt <= time() - 60)) {
        $resetToken = bin2hex(random_bytes(32));
        $resetTokenHash = hash('sha256', $resetToken);
        $resetExpires = date('Y-m-d H:i:s', time() + 3600);
        $updateStmt = $conn->prepare(
            'UPDATE users
             SET password_reset_token = ?, password_reset_expires = ?, password_reset_sent_at = NOW()
             WHERE user_id = ?'
        );

        if ($updateStmt) {
            $updateStmt->bind_param('ssi', $resetTokenHash, $resetExpires, $user['user_id']);
            if ($updateStmt->execute()) {
                sendApplicationEmail($email, $user['full_name'], 'password_reset', [
                    'action_url' => APP_URL . '/auth/reset_password.php?token=' . urlencode($resetToken),
                ]);
            }
            $updateStmt->close();
        }
    }
}

$_SESSION['success'] = $genericMessage;
redirect('forgot_password.php');