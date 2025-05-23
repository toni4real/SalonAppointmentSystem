<?php
session_start();
require_once '../includes/db_connection.php';

$unreadCount = 0;

if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
    $result = mysqli_query($conn, "
        SELECT COUNT(*) AS unread_count 
        FROM notifications 
        WHERE customer_id = $customer_id AND is_read = 0
    ");
    if ($result) {
        $data = mysqli_fetch_assoc($result);
        $unreadCount = $data['unread_count'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Help & Contact Us</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../customer/css/help.css">
</head>
<body>

<div class="sidebar">
    <h5 class="fw-bold mb-4">Salon Customer Panel</h5>
    <a class="nav-link" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a>
    <a class="nav-link" href="customer_dashboard.php"><i class="bi bi-speedometer2"></i> Your Appointments</a>
    <a class="nav-link" href="appointment_booking.php"><i class="bi bi-calendar-plus-fill"></i> Book Appointment</a>
    <a class="nav-link" href="customer_history.php"><i class="bi bi-clock-history"></i> Appointment History</a>

    <!-- Styled Notifications link without "active" class -->
    <a class="nav-link position-relative" href="notifications.php">
        <i class="bi bi-bell"></i> Notifications
        <?php if ($unreadCount > 0): ?>
            <span class="position-absolute top-50 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $unreadCount ?>
            </span>
        <?php endif; ?>
    </a>

    <!-- Help is active -->
    <a class="nav-link active" href="help.php"><i class="bi bi-question-circle"></i> Help</a>
    <a class="btn btn-danger text-white" href="customer_logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<div class="main-content">
    <h3>Need Help?</h3>
    <p>If you need assistance with booking, payments, or your account, feel free to reach out!</p>

    <ul>
    <li>Email: <a href="mailto:marketing@theatriumsalons.com">marketing@theatriumsalons.com</a></li>
        <li>Phone: +63 951 784 4284</li>
        <li>Facebook: <a href="https://www.facebook.com/TouchedbyAtriumCastillejos" target="_blank">https://www.facebook.com/TouchedbyAtriumCastillejos</a></li>
        <li>Operating Hours: Mon - Sat, 9:00 AM to 6:00 PM</li>
    </ul>

    <p>You can also message us via the contact form or our Facebook page.</p>
</div>

</body>
</html>
