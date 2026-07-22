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
 * Return a greeting message based on the current time of day.
 *
 * @param string $name
 * @return string
 */
function getTimeOfDayGreeting(string $name): string
{
    $hour = (int) date('G');
    if ($hour < 12) {
        $greeting = 'Good Morning';
    } elseif ($hour < 18) {
        $greeting = 'Good Afternoon';
    } else {
        $greeting = 'Good Evening';
    }

    return sprintf('%s, %s 👋', $greeting, $name);
}

/**
 * Calculate graduate profile completion as a percentage.
 *
 * @param array $status
 * @return int
 */
function calculateProfileCompletion(array $status): int
{
    $score = 0;
    $score += !empty($status['personal']) ? 20 : 0;
    $score += !empty($status['photo']) ? 10 : 0;
    $score += !empty($status['location']) ? 10 : 0;
    $score += !empty($status['bio']) ? 10 : 0;
    $score += !empty($status['education']) ? 20 : 0;
    $score += !empty($status['experience']) ? 10 : 0;
    $score += !empty($status['skills']) ? 10 : 0;
    $score += !empty($status['cv']) ? 10 : 0;

    return min(100, max(0, $score));
}

/**
 * Check whether a table column exists in the current database.
 *
 * @param mysqli $conn
 * @param string $table
 * @param string $column
 * @return bool
 */
function columnExists(mysqli $conn, string $table, string $column): bool
{
    $sql = 'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?';
    $stmt = $conn->prepare($sql);
    $dbName = DB_NAME;
    $stmt->bind_param('sss', $dbName, $table, $column);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
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
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'message' => 'File upload failed. Please try again.',
            'path' => null,
        ];
    }

    if ($file['size'] > $maxSize) {
        return [
            'success' => false,
            'message' => 'File exceeds the maximum allowed size.',
            'path' => null,
        ];
    }

    $fileName = $file['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedTypes, true)) {
        return [
            'success' => false,
            'message' => 'Invalid file type. Please upload a supported format.',
            'path' => null,
        ];
    }

    if (!is_dir($destinationDir) && !mkdir($destinationDir, 0755, true)) {
        return [
            'success' => false,
            'message' => 'Unable to create upload directory.',
            'path' => null,
        ];
    }

    $safeName = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', pathinfo($fileName, PATHINFO_FILENAME));
    $uniqueFileName = sprintf('%s_%s.%s', time(), $safeName, $fileExtension);
    $destination = rtrim($destinationDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $uniqueFileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success' => false,
            'message' => 'Unable to move the uploaded file.',
            'path' => null,
        ];
    }

    return [
        'success' => true,
        'message' => 'File uploaded successfully.',
        'path' => $uniqueFileName,
    ];
}

/**
 * Return the graduate_id for the current user, or null if not found.
 *
 * @param mysqli $conn
 * @param int $userId
 * @return int|null
 */
function getGraduateIdByUserId(mysqli $conn, int $userId): ?int
{
    $stmt = $conn->prepare('SELECT graduate_id FROM graduates WHERE user_id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($graduateId);
    $stmt->fetch();
    $stmt->close();

    return $graduateId !== null ? (int) $graduateId : null;
}

/**
 * Refresh the graduate profile completion score and update the graduates table if supported.
 *
 * @param mysqli $conn
 * @param int $userId
 * @return void
 */
function refreshGraduateProfileCompletion(mysqli $conn, int $userId): void
{
    $graduateId = getGraduateIdByUserId($conn, $userId);
    if (!$graduateId) {
        return;
    }

    $educationCount = 0;
    $experienceCount = 0;
    $skillCount = 0;
    $cv = null;
    $profilePicture = null;
    $fullName = null;
    $email = null;
    $phone = null;
    $location = null;
    $bio = null;

    $stmt = $conn->prepare('SELECT full_name, email, phone FROM users WHERE user_id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($fullName, $email, $phone);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare('SELECT location, bio, cv, profile_picture FROM graduates WHERE graduate_id = ? LIMIT 1');
    $stmt->bind_param('i', $graduateId);
    $stmt->execute();
    $stmt->bind_result($location, $bio, $cv, $profilePicture);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare('SELECT COUNT(*) FROM education WHERE graduate_id = ?');
    $stmt->bind_param('i', $graduateId);
    $stmt->execute();
    $stmt->bind_result($educationCount);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare('SELECT COUNT(*) FROM experience WHERE graduate_id = ?');
    $stmt->bind_param('i', $graduateId);
    $stmt->execute();
    $stmt->bind_result($experienceCount);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare('SELECT COUNT(*) FROM graduate_skills WHERE graduate_id = ?');
    $stmt->bind_param('i', $graduateId);
    $stmt->execute();
    $stmt->bind_result($skillCount);
    $stmt->fetch();
    $stmt->close();

    $completionStatus = [
        'personal' => !empty($fullName) && !empty($email) && !empty($phone),
        'photo' => !empty($profilePicture),
        'location' => !empty($location),
        'bio' => !empty($bio),
        'education' => $educationCount > 0,
        'experience' => $experienceCount > 0,
        'skills' => $skillCount > 0,
        'cv' => !empty($cv),
    ];

    $completion = calculateProfileCompletion($completionStatus);
    if (columnExists($conn, 'graduates', 'profile_completion')) {
        $stmt = $conn->prepare('UPDATE graduates SET profile_completion = ? WHERE graduate_id = ?');
        $stmt->bind_param('ii', $completion, $graduateId);
        $stmt->execute();
        $stmt->close();
    }
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
