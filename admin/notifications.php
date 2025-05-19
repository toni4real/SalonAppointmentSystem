<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Fetch admin name
$stmt = $conn->prepare("SELECT first_name FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$adminData = $result->fetch_assoc();
$firstName = explode(' ', $adminData['first_name'])[0];

// Get unread count
$countStmt = $conn->prepare("SELECT COUNT(*) AS unread_count FROM admin_notifications WHERE is_read = 0 AND customer_id IN (SELECT customer_id FROM customers)");
$countStmt->execute();
$countResult = $countStmt->get_result()->fetch_assoc();
$unreadCount = $countResult['unread_count'];

// Fetch notifications with service_name
$notifQuery = $conn->prepare("
    SELECT an.notification_id, an.message, an.notification_date, an.service_name, c.first_name, c.last_name
    FROM admin_notifications an
    JOIN customers c ON an.customer_id = c.customer_id
    ORDER BY an.notification_date DESC
");
$notifQuery->execute();
$notificationsResult = $notifQuery->get_result();
$allNotifications = $notificationsResult->fetch_all(MYSQLI_ASSOC);

// Mark all as read
$conn->query("UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0");
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
    <a class="nav-link <?= ($current_page == 'admin_walkins.php') ? 'active' : ''; ?>" href="admin_walkins.php">
        <i class="bi bi-door-open"></i> Walk-ins
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
    <a class="nav-link <?= ($current_page == 'notifications.php') ? 'active' : ''; ?>" href="notifications.php">
        <i class="bi bi-bell-fill"></i> Notifications
        <?php if ($unreadCount > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $unreadCount ?>
            </span>
        <?php endif; ?>
    </a>
    <a class="nav-link <?= ($current_page == 'admin_help.php') ? 'active' : ''; ?>" href="admin_help.php">
      <i class="bi bi-question-circle"></i> Help
    </a>
    <a class="nav-link btn btn-danger mt-auto text-white" href="admin_logout.php">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

    <div class="main-content">
        <h2>Notifications</h2>
        <hr>
        <div class="card mt-4">
            <div class="card-header text-white">Recent Notifications</div>
            <ul class="list-group list-group-flush">
                <?php if (!empty($allNotifications)): ?>
                    <?php foreach ($allNotifications as $notif): ?>
                        <?php
                            $icon = 'bi-info-circle text-info';
                            if ($notif['service_name'] === 'Appointment Cancelled') {
                                $icon = 'bi-exclamation-circle-fill text-danger';
                            } elseif ($notif['service_name'] === 'New Appointment Booked') {
                                $icon = 'bi-exclamation-octagon-fill text-warning';
                            }
                        ?>
                        <li class="list-group-item">
                            <i class="bi <?= $icon ?> me-2"></i>
                            <?= htmlspecialchars($notif['message']) ?>
                            <span class="text-muted float-end">
                                <?= date('F j, Y / g:i A', strtotime($notif['notification_date'])) ?>
                            </span>
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
