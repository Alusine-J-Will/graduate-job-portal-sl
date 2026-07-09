<?php
/**
 * Reusable helper functions for the Graduate Job Portal.
 */

/**
 * Sanitize text input for safe output.
 *
 * @param string $input
 * @return string
 */
function sanitizeInput(string $input): string
{
    return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
}
function displayFlashMessages(): void
{
    if (isset($_SESSION['success'])) {
        echo displaySuccess($_SESSION['success']);
        unset($_SESSION['success']);
    }

    if (isset($_SESSION['error'])) {
        echo displayError($_SESSION['error']);
        unset($_SESSION['error']);
    }
}

function displayWarning(string $message): string
{
    return '<div class="alert alert-warning" role="alert">'
        . sanitizeInput($message)
        . '</div>';
}

/**
 * Redirect to a different URL.
 *
 * @param string $url
 * @return void
 */
function redirect(string $url): void
{
    if (!headers_sent()) {
        header('Location: ' . $url);
        exit;
    }
}

/**
 * Check whether the user is currently logged in.
 *
 * @return bool
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check whether the current user has a given role.
 *
 * @param string $role
 * @return bool
 */
function hasRole(string $role): bool
{
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Return formatted error markup.
 *
 * @param string $message
 * @return string
 */
function displayError(string $message): string
{
    return '<div class="alert alert-danger" role="alert">' . sanitizeInput($message) . '</div>';
}

/**
 * Return formatted success markup.
 *
 * @param string $message
 * @return string
 */
function displaySuccess(string $message): string
{
    return '<div class="alert alert-success" role="alert">' . sanitizeInput($message) . '</div>';
}

/**
 * Format a datetime string into a readable date.
 *
 * @param string $datetime
 * @return string
 */
function formatDate(string $datetime): string
{
    $date = new DateTime($datetime);
    return $date->format('F j, Y');
}

/**
 * Handle file uploads.
 *
 * @param array $file
 * @param string $destinationDir
 * @param array $allowedTypes
 * @param int $maxSize
 * @return array
 */
function uploadFile(array $file, string $destinationDir, array $allowedTypes, int $maxSize): array
{
    $result = [
        'success' => false,
        'message' => 'Upload functionality is not implemented yet.',
        'path' => null,
    ];

    // Placeholder: add validation and move_uploaded_file handling here later.
    return $result;
}

/**
 * Prepare a notification record.
 *
 * @param int $userId
 * @param string $type
 * @param string $title
 * @param string $message
 * @param string|null $link
 * @return array
 */
function generateNotification(int $userId, string $type, string $title, string $message, ?string $link = null): array
{
    return [
        'user_id' => $userId,
        'type' => sanitizeInput($type),
        'title' => sanitizeInput($title),
        'message' => sanitizeInput($message),
        'link' => $link ? sanitizeInput($link) : null,
        'created_at' => date('Y-m-d H:i:s'),
    ];
}

/**
 * Log a user activity entry.
 *
 * @param int $userId
 * @param string $activity
 * @param string|null $ipAddress
 * @return array
 */
function logActivity(int $userId, string $activity, ?string $ipAddress = null): array
{
    return [
        'user_id' => $userId,
        'activity' => sanitizeInput($activity),
        'ip_address' => $ipAddress ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        'created_at' => date('Y-m-d H:i:s'),
    ];
}
