<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Fetch notifications
$query = "SELECT * FROM notifications WHERE customer_id = $customer_id ORDER BY notification_date DESC";
$result = mysqli_query($conn, $query);
if (!$result) {
    die('Error fetching notifications: ' . mysqli_error($conn));
}
$notifications = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Count unread notifications
$unreadResult = mysqli_query($conn, "
    SELECT COUNT(*) AS unread_count 
    FROM notifications 
    WHERE customer_id = $customer_id AND is_read = 0
");
$unreadData = mysqli_fetch_assoc($unreadResult);
$unreadCount = $unreadData['unread_count'];

// Mark all notifications as read
mysqli_query($conn, "
    UPDATE notifications 
    SET is_read = 1 
    WHERE customer_id = $customer_id AND is_read = 0
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../customer/css/notifications.css">
</head>
<body>
<div class="sidebar">
    <h5 class="fw-bold mb-4">Salon Customer Panel</h5>
    <a class="nav-link" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a>
    <a class="nav-link" href="customer_dashboard.php"><i class="bi bi-speedometer2"></i> Your Appointments</a>
    <a class="nav-link" href="appointment_booking.php"><i class="bi bi-calendar-plus-fill"></i> Book Appointment</a>
    <a class="nav-link" href="customer_history.php"><i class="bi bi-clock-history"></i> Appointment History</a>

    <!-- Notifications link with red badge -->
    <a class="nav-link active position-relative" href="notifications.php">
        <i class="bi bi-bell"></i> Notifications
        <?php if ($unreadCount > 0): ?>
            <span class="position-absolute top-50 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $unreadCount ?>
            </span>
        <?php endif; ?>
    </a>

    <a class="nav-link" href="help.php"><i class="bi bi-question-circle"></i> Help</a>
    <a class="btn btn-danger text-white" href="customer_logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<div class="main-content">
    <div class="container">
        <h2 class="text-center">Notifications</h2>

        <?php if (empty($notifications)): ?>
            <div class="alert alert-info">You have no new notifications.</div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item">
                    <h5><?= htmlspecialchars($notification['service_name'] ?? 'Notification') ?></h5>
                    <p><?= htmlspecialchars($notification['message'] ?? '') ?></p>
                    <p class="notification-date"><?= date('F j, Y, g:i a', strtotime($notification['notification_date'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
