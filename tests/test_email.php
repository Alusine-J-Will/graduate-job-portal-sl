<?php
/**
 * Temporary Gmail SMTP test. Run from the project root:
 * php tests/test_email.php recipient@example.com
 *
 * The recipient can also be supplied through GRADCONNECT_SMTP_TEST_RECIPIENT.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/email_helper.php';

$recipientEmail = $argv[1] ?? getenv('GRADCONNECT_SMTP_TEST_RECIPIENT') ?: '';

if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "FAILURE: Provide a valid recipient email as an argument or set GRADCONNECT_SMTP_TEST_RECIPIENT.\n");
    exit(1);
}

if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    fwrite(STDERR, "FAILURE: PHPMailer not found or Composer autoload could not be loaded.\n");
    exit(1);
}

if (!defined('SMTP_HOST') || SMTP_HOST !== 'smtp.gmail.com') {
    fwrite(STDERR, "FAILURE: Invalid SMTP configuration. SMTP_HOST must be smtp.gmail.com.\n");
    exit(1);
}

if (!defined('SMTP_PORT') || SMTP_PORT !== 587 || !defined('SMTP_ENCRYPTION') || strtolower(SMTP_ENCRYPTION) !== 'tls' || !defined('SMTP_AUTH') || SMTP_AUTH !== true) {
    fwrite(STDERR, "FAILURE: Invalid SMTP configuration. Expected Gmail port 587, TLS, and authentication enabled.\n");
    exit(1);
}

if (!defined('SMTP_USERNAME') || SMTP_USERNAME !== 'gradconnectsl.notifications@gmail.com') {
    fwrite(STDERR, "FAILURE: Invalid SMTP configuration. SMTP_USERNAME does not match the configured Gmail account.\n");
    exit(1);
}

if (!defined('SMTP_PASSWORD') || SMTP_PASSWORD === '') {
    fwrite(STDERR, "FAILURE: Missing GRADCONNECT_SMTP_PASSWORD.\n");
    exit(1);
}

$sent = sendHtmlEmail(
    $recipientEmail,
    'SMTP Test Recipient',
    'GradConnect SL SMTP Test',
    'This is a test email from the GradConnect SL development environment. Gmail SMTP and PHPMailer are working correctly.'
);

if ($sent) {
    echo "SUCCESS: SMTP test email sent to {$recipientEmail}.\n";
    exit(0);
}

fwrite(STDERR, "FAILURE: Email could not be sent. Check SMTP authentication, connection, TLS settings, and the Apache/PHP error log. The SMTP password was not displayed.\n");
exit(1);