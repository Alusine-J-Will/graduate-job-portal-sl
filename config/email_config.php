<?php
/**
 * Email configuration for GradConnect SL.
 *
 * SMTP settings are loaded from environment variables when available.
 * The Gmail App Password must be supplied through GRADCONNECT_SMTP_PASSWORD.
 */

if (!defined('EMAIL_ENABLED')) {
    define('EMAIL_ENABLED', getenv('GRADCONNECT_EMAIL_ENABLED') !== false ? filter_var(getenv('GRADCONNECT_EMAIL_ENABLED'), FILTER_VALIDATE_BOOLEAN) : true);
}

if (!defined('EMAIL_FROM_NAME')) {
    define('EMAIL_FROM_NAME', getenv('GRADCONNECT_EMAIL_FROM_NAME') ?: 'GradConnect SL');
}

if (!defined('EMAIL_FROM_ADDRESS')) {
    define('EMAIL_FROM_ADDRESS', getenv('GRADCONNECT_EMAIL_FROM_ADDRESS') ?: 'gradconnectsl.notifications@gmail.com');
}

if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', getenv('GRADCONNECT_SMTP_HOST') ?: 'smtp.gmail.com');
}

if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', (int) (getenv('GRADCONNECT_SMTP_PORT') ?: 587));
}

if (!defined('SMTP_USERNAME')) {
    define('SMTP_USERNAME', getenv('GRADCONNECT_SMTP_USERNAME') ?: 'gradconnectsl.notifications@gmail.com');
}

if (!defined('SMTP_PASSWORD')) {
    define('SMTP_PASSWORD', getenv('GRADCONNECT_SMTP_PASSWORD') ?: '');
}

if (!defined('SMTP_ENCRYPTION')) {
    define('SMTP_ENCRYPTION', getenv('GRADCONNECT_SMTP_ENCRYPTION') ?: 'tls');
}

if (!defined('SMTP_AUTH')) {
    define('SMTP_AUTH', getenv('GRADCONNECT_SMTP_AUTH') !== false ? filter_var(getenv('GRADCONNECT_SMTP_AUTH'), FILTER_VALIDATE_BOOLEAN) : true);
}

if (!defined('APP_URL')) {
    $configuredAppUrl = trim((string) getenv('GRADCONNECT_APP_URL'));
    if ($configuredAppUrl !== '') {
        $applicationUrl = rtrim($configuredAppUrl, '/');
    } else {
        $requestScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $requestHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $applicationUrl = $requestScheme . '://' . $requestHost . rtrim(BASE_URL, '/');
    }

    define('APP_URL', $applicationUrl);
}
