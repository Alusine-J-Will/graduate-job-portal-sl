<?php
/**
 * Reusable email helper for GradConnect SL.
 *
 * Uses PHPMailer if available. If not installed or SMTP is not configured,
 * the system logs the failure and continues gracefully.
 */

require_once __DIR__ . '/../config/email_config.php';

$composerAutoloader = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoloader)) {
    require_once $composerAutoloader;
}

if (!function_exists('sendHtmlEmail')) {
    function sendHtmlEmail(string $toEmail, string $toName, string $subject, string $message, ?string $actionUrl = null): bool
    {
        if (!defined('EMAIL_ENABLED') || !EMAIL_ENABLED) {
            return true;
        }

        if (empty($toEmail)) {
            error_log('Email send skipped: missing recipient address.');
            return false;
        }

        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->Port = SMTP_PORT;
                $mail->SMTPAuth = SMTP_AUTH;
                if (SMTP_AUTH) {
                    $mail->Username = SMTP_USERNAME;
                    $mail->Password = SMTP_PASSWORD;
                }
                if (!empty(SMTP_ENCRYPTION)) {
                    $mail->SMTPSecure = SMTP_ENCRYPTION;
                }
                $mail->setFrom(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
                $mail->addAddress($toEmail, $toName);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = buildEmailTemplate($subject, $message, $actionUrl);
                $mail->AltBody = strip_tags($message)
                    . ($actionUrl ? "\n\nVerification link: " . $actionUrl : '');
                $mail->send();
                return true;
            } catch (Throwable $e) {
                error_log('PHPMailer send failed: ' . $e->getMessage());
            }
        }

        if (empty(SMTP_HOST) || empty(SMTP_USERNAME) || empty(SMTP_PASSWORD)) {
            error_log('Email send skipped: Gmail SMTP credentials are incomplete.');
            return false;
        }

        error_log('Email send failed: PHPMailer is unavailable while SMTP_HOST is configured.');
        return false;
    }
}

if (!function_exists('buildEmailTemplate')) {
    function buildEmailTemplate(string $subject, string $message, ?string $actionUrl = null): string
    {
        $safeSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
        $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
        $safeActionUrl = htmlspecialchars($actionUrl ?: APP_URL, ENT_QUOTES, 'UTF-8');
        $actionLabel = $actionUrl ? 'Verify My Email' : 'Open GradConnect SL';
        $fallbackUrl = $actionUrl
            ? '<p style="margin:0;color:#4b5563;font-size:13px;line-height:1.5;">If the button does not work, copy and paste this link into your browser:<br><a href="%s" style="color:#0d6efd;word-break:break-all;">%s</a></p>'
            : '';

        return sprintf(
            '<!DOCTYPE html>
            <html lang="en">
            <body style="margin:0;padding:0;background:#f7f9fc;font-family:Arial,sans-serif;color:#1f2937;">
            <table role="presentation" width="100%%" cellspacing="0" cellpadding="0" border="0" style="background:#f7f9fc;padding:24px 0;">
                <tr>
                    <td align="center">
                        <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 6px 20px rgba(0,0,0,0.06);">
                            <tr>
                                <td style="background:#0d6efd;padding:24px 30px;color:#ffffff;">
                                    <h1 style="margin:0;font-size:24px;">Graduate Job Portal SL</h1>
                                    <p style="margin:8px 0 0;opacity:0.9;">Your trusted graduate career platform</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:30px;">
                                    <h2 style="margin:0 0 12px;font-size:20px;color:#111827;">%s</h2>
                                    <p style="margin:0 0 20px;line-height:1.6;color:#4b5563;">%s</p>
                                    <p style="margin:0 0 24px;">
                                        <a href="%s" style="display:inline-block;padding:12px 20px;background:#0d6efd;color:#ffffff;text-decoration:none;border-radius:8px;">%s</a>
                                    </p>
                                    %s
                                </td>
                            </tr>
                            <tr>
                                <td style="background:#f3f4f6;padding:20px 30px;color:#6b7280;font-size:12px;line-height:1.6;">
                                    This email was sent by Graduate Job Portal SL. Please do not reply to this message.
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            </body>
            </html>',
            $safeSubject,
            $safeMessage,
            $safeActionUrl,
            $actionLabel,
            $actionUrl ? sprintf($fallbackUrl, $safeActionUrl, $safeActionUrl) : ''
        );
    }
}

if (!function_exists('sendApplicationEmail')) {
    function sendApplicationEmail(string $recipientEmail, string $recipientName, string $eventType, array $data = []): void
    {
        $subject = '';
        $message = '';

        switch ($eventType) {
            case 'new_application':
                $subject = 'New Job Application Received';
                $message = 'A new application has been submitted for your job posting.';
                break;
            case 'application_reviewed':
                $subject = 'Application Reviewed';
                $message = 'Your application has been reviewed.';
                break;
            case 'application_shortlisted':
                $subject = 'Application Shortlisted';
                $message = 'Congratulations! Your application has been shortlisted.';
                break;
            case 'application_accepted':
                $subject = 'Application Accepted';
                $message = 'Congratulations! Your application has been accepted.';
                break;
            case 'application_rejected':
                $subject = 'Application Update';
                $message = 'Your application was not selected at this time.';
                break;
            case 'company_approved':
                $subject = 'Company Verification Approved';
                $message = 'Your company profile has been approved.';
                break;
            case 'company_rejected':
                $subject = 'Company Verification Update';
                $message = 'Please review your company information and resubmit.';
                break;
            case 'job_match':
                $subject = 'New Job Match Found';
                $message = 'A new job matching your profile is available.';
                break;
            case 'welcome':
                $subject = 'Welcome to GradConnect SL';
                $message = 'Welcome to Graduate Job Portal SL. We are excited to have you on board.';
                break;
            case 'email_verification':
                $subject = 'Verify Your GradConnect SL Account';
                $recipientType = !empty($data['account_type']) ? $data['account_type'] : 'account';
                $message = sprintf(
                    'Hello %s, your %s has been created. Please verify this email address by clicking the button below. This link expires in 24 hours. If you did not create this account, you can safely ignore this email.',
                    $recipientName,
                    $recipientType
                );
                break;
            default:
                return;
        }

        if (!empty($data['job_title'])) {
            $message .= ' Job: ' . $data['job_title'];
        }

        if (!empty($data['company_name'])) {
            $message .= ' Company: ' . $data['company_name'];
        }

        if (!empty($data['application_id'])) {
            $message .= ' Application ID: ' . $data['application_id'];
        }

        sendHtmlEmail($recipientEmail, $recipientName, $subject, $message, $data['action_url'] ?? null);
    }
}
