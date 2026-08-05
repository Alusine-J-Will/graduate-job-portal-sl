<?php
/**
 * Notification center - lists notifications for the logged-in user.
 *
 * Requirements satisfied:
 * - Requires authentication
 * - Uses prepared statements
 * - Pagination (10 per page)
 * - Filters: all, unread, read
 * - Actions: mark individual read (AJAX target), mark all read
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Ensure user is logged in (auth_check already redirects unauthenticated users)
session_write_close(); // close write lock early

$userId = (int) ($_SESSION['user_id'] ?? 0);
if ($userId <= 0) {
    redirect(BASE_URL . 'auth/login.php');
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'unread_count') {
    header('Content-Type: application/json');
    $cntStmt = $conn->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    if ($cntStmt !== false) {
        $cntStmt->bind_param('i', $userId);
        $cntStmt->execute();
        $cntStmt->bind_result($unreadCount);
        $cntStmt->fetch();
        $cntStmt->close();
    }

    echo json_encode(['unread_count' => (int) $unreadCount]);
    exit;
}

$filter = $_GET['filter'] ?? 'all';
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Count total notifications for pagination
$countSql = 'SELECT COUNT(*) FROM notifications WHERE user_id = ?';
if ($filter === 'unread') {
    $countSql .= ' AND is_read = 0';
} elseif ($filter === 'read') {
    $countSql .= ' AND is_read = 1';
}

$stmt = $conn->prepare($countSql);
if ($stmt === false) {
    $_SESSION['error'] = 'Unable to load notifications.';
    redirect(BASE_URL . 'dashboard.php');
}
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->bind_result($totalCount);
$stmt->fetch();
$stmt->close();

$totalPages = (int) ceil($totalCount / $perPage);

// Fetch notifications
$sql = 'SELECT notification_id, type, title, message, link, is_read, created_at FROM notifications WHERE user_id = ?';
if ($filter === 'unread') {
    $sql .= ' AND is_read = 0';
} elseif ($filter === 'read') {
    $sql .= ' AND is_read = 1';
}
$sql .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    $_SESSION['error'] = 'Unable to load notifications.';
    redirect(BASE_URL . 'dashboard.php');
}
$stmt->bind_param('iii', $userId, $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

$notifications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Unread count for bell
$cntStmt = $conn->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
if ($cntStmt !== false) {
    $cntStmt->bind_param('i', $userId);
    $cntStmt->execute();
    $cntStmt->bind_result($unreadCount);
    $cntStmt->fetch();
    $cntStmt->close();
}

?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/dashboard_topbar.php'; ?>

<main class="container my-4">
    <div class="row">
        <div class="col-lg-3">
            <?php
            $sidebarInclude = __DIR__ . '/../includes/sidebar.php';
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                $sidebarInclude = __DIR__ . '/../includes/admin_sidebar.php';
            }
            include $sidebarInclude;
            ?>
        </div>
        <div class="col-lg-9">
            <div class="section-header mb-3">
                <div>
                    <h2 class="section-heading">Notifications</h2>
                    <p class="section-subtitle">Manage your activity updates and alerts</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="?filter=all" class="btn btn-outline-custom <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
                    <a href="?filter=unread" class="btn btn-outline-custom <?php echo $filter === 'unread' ? 'active' : ''; ?>">Unread</a>
                    <a href="?filter=read" class="btn btn-outline-custom <?php echo $filter === 'read' ? 'active' : ''; ?>">Read</a>
                    <form method="post" action="mark_all_read.php" class="ms-2">
                        <button type="submit" class="btn btn-primary-custom">Mark All Read</button>
                    </form>
                </div>
            </div>

            <?php displayFlashMessages(); ?>

            <?php if (empty($notifications)): ?>
                <div class="card-ui p-4 text-center">
                    <p class="mb-0">No notifications available.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $note): ?>
                    <?php $isUnread = (int) $note['is_read'] === 0; ?>
                    <div class="card-ui mb-3 p-3 <?php echo $isUnread ? 'bg-light-blue' : ''; ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-1 <?php echo $isUnread ? 'fw-bold' : ''; ?>"><?php echo sanitizeInput($note['title']); ?></h5>
                                <p class="mb-1 text-muted small"><?php echo sanitizeInput($note['type'] ?? 'General'); ?> • <?php echo formatDate($note['created_at']); ?></p>
                                <p class="mb-0"><?php echo sanitizeInput($note['message']); ?></p>
                            </div>
                            <div class="text-end">
                                <?php if ($isUnread): ?>
                                    <form method="post" action="mark_read.php" class="mb-2">
                                        <input type="hidden" name="notification_id" value="<?php echo (int) $note['notification_id']; ?>">
                                        <button type="submit" class="btn btn-outline-custom btn-sm">Mark Read</button>
                                    </form>
                                <?php endif; ?>
                                <?php if (!empty($note['link'])): ?>
                                    <a href="<?php echo sanitizeInput(resolveNotificationLink($note['link'])); ?>" class="btn btn-primary-custom btn-sm" target="_self">View</a>
                                <?php else: ?>
                                    <button class="btn btn-outline-custom btn-sm" disabled>View</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Pagination -->
                <nav aria-label="Notifications pagination">
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= max(1, $totalPages); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>"><a class="page-link" href="?filter=<?php echo urlencode($filter); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

