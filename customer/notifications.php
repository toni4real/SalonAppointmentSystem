<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Fetch notifications for the logged-in customer
$query = "
    SELECT * FROM notifications
    WHERE customer_id = $customer_id
    ORDER BY notification_date DESC
";
$result = mysqli_query($conn, $query);

if (!$result) {
    die('Error fetching notifications: ' . mysqli_error($conn));
}

$notifications = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            display: flex;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            width: 250px;
            background-color: #f77fbe;
            height: 100vh;
            position: fixed;
            padding: 20px 0;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sidebar .nav-link {
            color: white;
            padding: 12px 20px;
            width: 100%;
            text-align: left;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
        .sidebar .btn-danger {
            margin-top: auto;
            margin-bottom: 20px;
            width: 80%;
            border-radius: 20px;
        }
        .main-content {
            margin-left: 250px;
            padding: 40px;
            width: 100%;
        }
        .container {
            max-width: 800px;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.15);
            margin: auto;
        }
        h2 {
            color: #f77fbe;
            margin-bottom: 25px;
            text-align: center;
        }
        .notification-item {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .notification-item h5 {
            margin: 0;
            color: #f77fbe;
        }
        .notification-item p {
            margin: 5px 0;
        }
        .notification-date {
            font-size: 0.8rem;
            color: #777;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h5 class="fw-bold mb-4">Salon Customer Panel</h5>
    <a class="nav-link" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a>
    <a class="nav-link" href="customer_dashboard.php"><i class="bi bi-speedometer2"></i> Your Appointments</a>
    <a class="nav-link" href="appointment_booking.php"><i class="bi bi-calendar-plus-fill"></i> Book Appointment</a>
    <a class="nav-link" href="customer_history.php"><i class="bi bi-clock-history"></i> Appointment History</a>
    <a class="nav-link active" href="notifications.php"><i class="bi bi-bell"></i> Notifications</a>
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
