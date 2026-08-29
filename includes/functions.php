<?php
/**
 * Reusable helper functions for the Graduate Job Portal.
 */

require_once __DIR__ . '/email_helper.php';

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
    if ($stmt !== false) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->bind_result($fullName, $email, $phone);
        $stmt->fetch();
        $stmt->close();
    }

    $stmt = $conn->prepare('SELECT location, bio, cv, profile_picture FROM graduates WHERE graduate_id = ? LIMIT 1');
    if ($stmt !== false) {
        $stmt->bind_param('i', $graduateId);
        $stmt->execute();
        $stmt->bind_result($location, $bio, $cv, $profilePicture);
        $stmt->fetch();
        $stmt->close();
    }

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
 * Insert a notification record into the database.
 *
 * @param array $notification
 * @return bool
 */
function saveNotification(array $notification): bool
{
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare('INSERT INTO notifications (user_id, type, title, message, link, created_at) VALUES (?, ?, ?, ?, ?, ?)');
    if ($stmt === false) {
        return false;
    }

    $stmt->bind_param(
        'isssss',
        $notification['user_id'],
        $notification['type'],
        $notification['title'],
        $notification['message'],
        $notification['link'],
        $notification['created_at']
    );

    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Resolve a notification link to an absolute URL for rendering.
 *
 * @param string|null $link
 * @return string|null
 */
function resolveNotificationLink(?string $link): ?string
{
    if ($link === null) {
        return null;
    }

    $trimmedLink = trim($link);
    if ($trimmedLink === '') {
        return null;
    }

    // Preserve external URLs unchanged.
    if (stripos($trimmedLink, 'http://') === 0 || stripos($trimmedLink, 'https://') === 0) {
        return $trimmedLink;
    }

    $parsedUrl = parse_url($trimmedLink);
    $path = isset($parsedUrl['path']) ? ltrim($parsedUrl['path'], '/') : '';
    $queryParams = [];
    parse_str($parsedUrl['query'] ?? '', $queryParams);

    if (preg_match('#(?:^|/)(job_details\.php)$#i', $path, $matches)) {
        $jobId = null;
        if (!empty($queryParams['job_id']) && ctype_digit((string) $queryParams['job_id'])) {
            $jobId = (int) $queryParams['job_id'];
        } elseif (!empty($queryParams['id']) && ctype_digit((string) $queryParams['id'])) {
            $jobId = (int) $queryParams['id'];
        }

        $userRole = $_SESSION['role'] ?? null;
        if ($userRole === 'employer') {
            return BASE_URL . 'employer/job_details.php' . ($jobId ? '?job_id=' . $jobId : '');
        }

        if ($userRole === 'admin') {
            return BASE_URL . 'admin/job_details.php' . ($jobId ? '?job_id=' . $jobId : '');
        }

        return BASE_URL . 'graduate/job_details.php' . ($jobId ? '?job_id=' . $jobId : '');
    }

    if (strpos($trimmedLink, '/') === 0) {
        return $trimmedLink;
    }

    return BASE_URL . ltrim($trimmedLink, '/');
}

/**
 * Check whether a similar notification already exists for a user.
 *
 * @param mysqli $conn
 * @param int $userId
 * @param string $type
 * @param string $title
 * @param string|null $link
 * @return bool
 */
function notificationExists(mysqli $conn, int $userId, string $type, string $title, ?string $link = null): bool
{
    if ($link === null) {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND type = ? AND title = ? AND link IS NULL');
        $stmt->bind_param('iss', $userId, $type, $title);
    } else {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND type = ? AND title = ? AND link = ?');
        $stmt->bind_param('isss', $userId, $type, $title, $link);
    }

    if ($stmt === false) {
        return false;
    }

    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return (int) $count > 0;
}

/**
 * Normalize text for matching.
 *
 * @param string $text
 * @return string
 */
function normalizeText(string $text): string
{
    $normalized = mb_strtolower(trim($text));
    $normalized = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $normalized);
    return trim(preg_replace('/\s+/u', ' ', $normalized));
}

/**
 * Split a text string into normalized keyword tokens.
 *
 * @param string $text
 * @return array<int, string>
 */
function extractTokens(string $text): array
{
    $text = normalizeText($text);
    if ($text === '') {
        return [];
    }

    $tokens = preg_split('/\s+/u', $text);
    if (!is_array($tokens)) {
        return [];
    }

    $tokens = array_filter(array_unique($tokens), static function ($token): bool {
        return mb_strlen($token) > 1;
    });

    return array_values($tokens);
}

/**
 * Parse job skill text into normalized skill keywords.
 *
 * @param string|null $skills
 * @return array<int, string>
 */
function parseJobSkills(?string $skills): array
{
    if (empty($skills)) {
        return [];
    }

    $raw = preg_split('/[\r\n,;]+/', $skills);
    if (!is_array($raw)) {
        return [];
    }

    $parsed = array_map('normalizeText', $raw);
    $parsed = array_filter($parsed, static function ($value): bool {
        return $value !== '';
    });

    return array_values(array_unique($parsed));
}

/**
 * Execute a job matching query and return matched graduate user IDs.
 *
 * @param mysqli $conn
 * @param string $sql
 * @param array<int, string> $params
 * @return array<int, int>
 */
function bindStatementParams(mysqli_stmt $stmt, string $types, array $params): bool
{
    $refs = [];
    $refs[] = &$types;
    foreach ($params as $key => $value) {
        $refs[] = &$params[$key];
    }

    return call_user_func_array([$stmt, 'bind_param'], $refs);
}

function fetchMatchedGraduateUserIds(mysqli $conn, string $sql, array $params): array
{
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return [];
    }

    if (!bindStatementParams($stmt, str_repeat('s', count($params)), $params)) {
        $stmt->close();
        return [];
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $matched = [];
    while ($row = $result->fetch_assoc()) {
        $matched[] = (int) $row['user_id'];
    }
    $stmt->close();

    return $matched;
}

/**
 * Find graduates whose profile fields match the given job posting.
 *
 * @param mysqli $conn
 * @param array $job
 * @return array<int, int>
 */
function findMatchingGraduateUserIdsForJob(mysqli $conn, array $job): array
{
    $matchedUserIds = [];
    $jobSkills = parseJobSkills($job['skills'] ?? '');
    $jobCategory = normalizeText($job['category_name'] ?? '');
    $jobTitle = normalizeText($job['title'] ?? '');
    $jobEducation = normalizeText($job['education_level'] ?? '');
    $jobExperience = normalizeText($job['experience_level'] ?? '');

    $categoryTokens = extractTokens($jobCategory . ' ' . $jobTitle);
    $experienceTokens = extractTokens($jobExperience . ' ' . $jobTitle . ' ' . $jobCategory);

    if (!empty($jobSkills)) {
        $placeholders = implode(',', array_fill(0, count($jobSkills), '?'));
        $sql = "SELECT DISTINCT u.user_id
                FROM graduate_skills gs
                INNER JOIN skills s ON gs.skill_id = s.skill_id
                INNER JOIN graduates g ON g.graduate_id = gs.graduate_id
                INNER JOIN users u ON u.user_id = g.user_id
                WHERE u.role = 'graduate'
                  AND u.status = 'active'
                  AND LOWER(s.skill_name) IN ($placeholders)";

        $params = array_map('normalizeText', $jobSkills);
        $matchedUserIds = array_merge($matchedUserIds, fetchMatchedGraduateUserIds($conn, $sql, $params));
    }

    if ($jobEducation !== '' && mb_strtolower($jobEducation) !== 'any') {
        $degreeTerm = '%' . str_replace(' ', '%', $jobEducation) . '%';
        $sql = "SELECT DISTINCT u.user_id
                FROM education e
                INNER JOIN graduates g ON g.graduate_id = e.graduate_id
                INNER JOIN users u ON u.user_id = g.user_id
                WHERE u.role = 'graduate'
                  AND u.status = 'active'
                  AND (LOWER(e.degree) LIKE ? OR LOWER(e.field_of_study) LIKE ? OR LOWER(e.institution) LIKE ? )";

        $params = [$degreeTerm, $degreeTerm, $degreeTerm];
        $matchedUserIds = array_merge($matchedUserIds, fetchMatchedGraduateUserIds($conn, $sql, $params));
    }

    if (!empty($experienceTokens)) {
        $likeTerms = array_map(static function (string $token): string {
            return '%' . $token . '%';
        }, $experienceTokens);

        $conditions = [];
        $params = [];
        foreach ($likeTerms as $term) {
            $conditions[] = '(LOWER(ex.position) LIKE ? OR LOWER(ex.description) LIKE ? OR LOWER(ex.organization) LIKE ?)';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($conditions)) {
            $sql = "SELECT DISTINCT u.user_id
                    FROM experience ex
                    INNER JOIN graduates g ON g.graduate_id = ex.graduate_id
                    INNER JOIN users u ON u.user_id = g.user_id
                    WHERE u.role = 'graduate'
                      AND u.status = 'active'
                      AND (" . implode(' OR ', $conditions) . ')';

            $matchedUserIds = array_merge($matchedUserIds, fetchMatchedGraduateUserIds($conn, $sql, $params));
        }
    }

    if (!empty($categoryTokens)) {
        $likeTerms = array_map(static function (string $token): string {
            return '%' . $token . '%';
        }, $categoryTokens);

        $conditions = [];
        $params = [];
        foreach ($likeTerms as $term) {
            $conditions[] = '(LOWER(e.field_of_study) LIKE ? OR LOWER(e.degree) LIKE ? OR LOWER(ex.position) LIKE ? OR LOWER(ex.description) LIKE ? OR LOWER(ex.organization) LIKE ?)';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql = "SELECT DISTINCT u.user_id
                FROM graduates g
                LEFT JOIN education e ON e.graduate_id = g.graduate_id
                LEFT JOIN experience ex ON ex.graduate_id = g.graduate_id
                INNER JOIN users u ON u.user_id = g.user_id
                WHERE u.role = 'graduate'
                  AND u.status = 'active'
                  AND (" . implode(' OR ', $conditions) . ')';

        $matchedUserIds = array_merge($matchedUserIds, fetchMatchedGraduateUserIds($conn, $sql, $params));
    }

    return array_values(array_unique($matchedUserIds));
}

/**
 * Notify matching graduates when a new job is posted.
 *
 * @param mysqli $conn
 * @param array $job
 * @return void
 */
function notifyGraduatesOfMatchingJob(mysqli $conn, array $job): void
{
    $graduateUserIds = findMatchingGraduateUserIdsForJob($conn, $job);
    if (empty($graduateUserIds)) {
        return;
    }

    $link = BASE_URL . 'graduate/job_details.php?job_id=' . (int) $job['job_id'];
    $title = 'New Matching Job';
    $message = sprintf('A new %s position matches your profile and interests.', $job['title'] ?? 'job');

    foreach ($graduateUserIds as $userId) {
        if ($userId <= 0) {
            continue;
        }

        if (!notificationExists($conn, $userId, 'job_match', $title, $link)) {
            $notification = generateNotification($userId, 'job_match', $title, $message, $link);
            saveNotification($notification);
        }

        $userEmailStmt = $conn->prepare('SELECT email, full_name FROM users WHERE user_id = ? LIMIT 1');
        if ($userEmailStmt) {
            $userEmailStmt->bind_param('i', $userId);
            $userEmailStmt->execute();
            $userEmailResult = $userEmailStmt->get_result();
            $userAccount = $userEmailResult->fetch_assoc();
            $userEmailStmt->close();

            if (!empty($userAccount['email'])) {
                sendApplicationEmail(
                    $userAccount['email'],
                    $userAccount['full_name'] ?? 'Graduate',
                    'job_match',
                    ['job_title' => $job['title'] ?? '']
                );
            }
        }
    }
}

/**
 * Ensure job expiry notifications are created for an employer.
 *
 * @param mysqli $conn
 * @param int $companyId
 * @param int $userId
 * @return void
 */
function ensureJobExpiryNotifications(mysqli $conn, int $companyId, int $userId): void
{
    $today = date('Y-m-d');
    $deadlineBoundary = date('Y-m-d', strtotime('+7 days'));

    $stmt = $conn->prepare('SELECT job_id, title, deadline FROM jobs WHERE company_id = ? AND deadline IS NOT NULL');
    if ($stmt === false) {
        return;
    }

    $stmt->bind_param('i', $companyId);
    $stmt->execute();
    $result = $stmt->get_result();
    $jobs = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($jobs as $job) {
        $deadline = date('Y-m-d', strtotime($job['deadline']));
        $link = BASE_URL . 'employer/job_details.php?job_id=' . (int) $job['job_id'];

        if ($deadline < $today) {
            $title = 'Job Expired';
            $message = sprintf('Your %s job posting has expired.', $job['title']);
            $type = 'job_expiry';
        } elseif ($deadline <= $deadlineBoundary) {
            $title = 'Job Deadline Approaching';
            $message = sprintf('Your %s job posting will expire in 7 days.', $job['title']);
            $type = 'job_expiry';
        } else {
            continue;
        }

        if (!notificationExists($conn, $userId, $type, $title, $link)) {
            $notification = generateNotification($userId, $type, $title, $message, $link);
            saveNotification($notification);
        }
    }
}

/**
 * Send a notification to all admin users.
 *
 * @param mysqli $conn
 * @param string $type
 * @param string $title
 * @param string $message
 * @param string|null $link
 * @return void
 */
function notifyAdmins(mysqli $conn, string $type, string $title, string $message, ?string $link = null): void
{
    $stmt = $conn->prepare('SELECT user_id FROM users WHERE role = ?');
    $role = 'admin';
    if ($stmt === false) {
        return;
    }
    $stmt->bind_param('s', $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $admins = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($admins as $admin) {
        $adminId = (int) $admin['user_id'];
        if ($adminId <= 0) {
            continue;
        }

        // avoid duplicate identical notifications within short window
        if (!notificationExists($conn, $adminId, $type, $title, $link)) {
            $notification = generateNotification($adminId, $type, $title, $message, $link);
            saveNotification($notification);
        }
    }
}

/**
 * Ensure admin summary notifications are created when key system counts require attention.
 *
 * @param mysqli $conn
 * @return void
 */
function ensureAdminSummaryNotifications(mysqli $conn): void
{
    // Check key counts
    $pendingCompanies = 0;
    $inactiveEmployers = 0;
    $pendingApplications = 0;

    $stmt = $conn->prepare("SELECT COUNT(*) FROM companies WHERE verification_status = 'Pending'");
    if ($stmt) {
        $stmt->execute();
        $stmt->bind_result($pendingCompanies);
        $stmt->fetch();
        $stmt->close();
    }

    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'employer' AND status = 'inactive'");
    if ($stmt) {
        $stmt->execute();
        $stmt->bind_result($inactiveEmployers);
        $stmt->fetch();
        $stmt->close();
    }

    $stmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE status = 'Submitted'");
    if ($stmt) {
        $stmt->execute();
        $stmt->bind_result($pendingApplications);
        $stmt->fetch();
        $stmt->close();
    }

    if ($pendingCompanies > 0 || $inactiveEmployers > 0 || $pendingApplications > 50) {
        $title = 'Administrative Attention Required';
        $message = 'System items require administrative review.';
        $link = BASE_URL . 'admin/employers.php';
        notifyAdmins($conn, 'admin_summary', $title, $message, $link);
    }
}

/**
 * Flag a job for moderation (creates a moderation notification for admins).
 *
 * @param mysqli $conn
 * @param int $jobId
 * @param string|null $reason
 * @return void
 */
function flagJobForModeration(mysqli $conn, int $jobId, ?string $reason = null): void
{
    $title = 'Job Requires Review';
    $message = 'A job posting has been flagged for administrative review.' . ($reason ? ' Reason: ' . sanitizeInput($reason) : '');
    $link = BASE_URL . 'admin/job_details.php?job_id=' . $jobId;
    notifyAdmins($conn, 'admin_moderation', $title, $message, $link);
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
