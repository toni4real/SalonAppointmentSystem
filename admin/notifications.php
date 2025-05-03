<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Fetch admin name
$admin_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT first_name FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$adminData = $result->fetch_assoc();
$firstName = explode(' ', $adminData['first_name'])[0];

// Fetch recent notifications
$notifQuery = "
    SELECT n.notification_id, n.message, n.notification_date, c.first_name, c.last_name
    FROM notifications n
    JOIN customers c ON n.customer_id = c.customer_id
    ORDER BY n.notification_date DESC
";
$notificationsResult = $conn->query($notifQuery);

// Store notifications and IDs
$allNotifications = [];
$notifIDs = [];

while ($notif = $notificationsResult->fetch_assoc()) {
    $allNotifications[] = $notif;
    $notifIDs[] = $notif['notification_id'];
}

// Mark all unseen notifications as viewed by the admin
if (!empty($notifIDs)) {
    $values = [];
    foreach ($notifIDs as $id) {
        $values[] = "($id, $admin_id)";
    }

    $insertQuery = "
        INSERT IGNORE INTO admin_notification_views (notification_id, admin_id)
        VALUES " . implode(',', $values);
    $conn->query($insertQuery);
}

// Get unread notification count
$unreadCount = 0;
$countQuery = $conn->prepare("
    SELECT COUNT(*) AS unread_count
    FROM notifications n
    LEFT JOIN admin_notification_views av
    ON n.notification_id = av.notification_id AND av.admin_id = ?
    WHERE av.notification_id IS NULL
");
$countQuery->bind_param("i", $admin_id);
$countQuery->execute();
$countResult = $countQuery->get_result()->fetch_assoc();
$unreadCount = $countResult['unread_count'];

// Function to display time ago
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return $diff . ' seconds ago';
    elseif ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    elseif ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    else return floor($diff / 86400) . ' days ago';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Notifications</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../admin/css/notifications.css">
</head>
<body>

<div class="sidebar d-flex flex-column">
    <h4 class="text-white mb-4">Hi, <?= htmlspecialchars($firstName) ?> <span class="wave">👋</span></h4>
    <a class="nav-link <?= ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>" href="admin_dashboard.php">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a class="nav-link <?= ($current_page == 'admin_profile.php') ? 'active' : ''; ?>" href="admin_profile.php">
        <i class="bi bi-person-circle"></i> Profile
    </a>
    <a class="nav-link <?= ($current_page == 'admin_appointments.php') ? 'active' : ''; ?>" href="admin_appointments.php">
        <i class="bi bi-calendar-check"></i> Appointments
    </a>
    <a class="nav-link <?= ($current_page == 'payment_history.php') ? 'active' : ''; ?>" href="payment_history.php">
        <i class="bi bi-credit-card-2-front"></i> Payments
    </a>
    <a class="nav-link <?= ($current_page == 'staff_management.php') ? 'active' : ''; ?>" href="staff_management.php">
        <i class="bi bi-person-gear"></i> Staff Management
    </a>
    <a class="nav-link <?= ($current_page == 'staff_attendance.php') ? 'active' : ''; ?>" href="staff_attendance.php">
        <i class="bi bi-person-lines-fill"></i> Staff Attendance
    </a>
    <a class="nav-link <?= ($current_page == 'services_list.php') ? 'active' : ''; ?>" href="services_list.php">
        <i class="bi bi-stars"></i> Services
    </a>
    <a class="nav-link position-relative <?= ($current_page == 'notifications.php') ? 'active' : ''; ?>" href="notifications.php">
        <i class="bi bi-bell-fill text-white"></i> Notifications
        <?php if ($unreadCount > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $unreadCount ?>
            </span>
        <?php endif; ?>
    </a>
    <a class="nav-link btn btn-danger mt-auto text-white" href="admin_logout.php">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

<div class="main-content">
    <h2>Notifications</h2>
    <hr>
    <div class="card mt-4">
        <div class="card-header text-white">
            Recent Notifications
        </div>
        <ul class="list-group list-group-flush">
            <?php if (!empty($allNotifications)): ?>
                <?php foreach ($allNotifications as $notif): ?>
                    <li class="list-group-item">
                        <i class="bi bi-info-circle text-info me-2"></i>
                        <?= htmlspecialchars($notif['message']) ?>
                        <span class="text-muted float-end"><?= timeAgo($notif['notification_date']) ?></span>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="list-group-item">No notifications found.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>

</body>
</html>
