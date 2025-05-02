<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Fetch customer info
$query = mysqli_query($conn, "SELECT * FROM customers WHERE customer_id='$customer_id'");
$customer = mysqli_fetch_assoc($query);

// Fetch completed & paid or cancelled appointments
$appointmentQuery = mysqli_query($conn, "
    SELECT 
        a.appointment_date AS appointment_date,
        a.appointment_time AS appointment_time,
        s.service_name AS service_name,
        a.status AS status,
        a.payment_status AS payment_status
    FROM 
        appointments a
    JOIN 
        services s ON a.service_id = s.service_id
    WHERE 
        a.customer_id = '$customer_id'
        AND (a.status = 'Completed' AND a.payment_status = 'Paid' OR a.status = 'Cancelled')
    ORDER BY 
        a.appointment_date ASC, a.appointment_time ASC
");

// Count unread notifications
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
    <title>Appointment History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/customer_history.css">
</head>
<body>

<div class="sidebar">
    <h5 class="fw-bold mb-4">Salon Customer Panel</h5>
    <a class="nav-link" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a>
    <a class="nav-link" href="customer_dashboard.php"><i class="bi bi-speedometer2"></i> Your Appointments</a>
    <a class="nav-link" href="appointment_booking.php"><i class="bi bi-calendar-plus-fill"></i> Book Appointment</a>
    <a class="nav-link active" href="customer_history.php"><i class="bi bi-clock-history"></i> Appointment History</a>
    
    <!-- Styled Notifications -->
    <a class="nav-link position-relative" href="notifications.php">
        <i class="bi bi-bell"></i> Notifications
        <?php if ($unreadCount > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $unreadCount ?>
            </span>
        <?php endif; ?>
    </a>


    <a class="nav-link" href="help.php"><i class="bi bi-question-circle"></i> Help</a>
    <a class="btn btn-danger text-white" href="customer_logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<div class="main-content">
    <div class="welcome-message text-center mb-4">
        <h2>Welcome To Appointment History</h2>
    </div>

    <div class="table-container">
        <h4>Your History</h4>
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Payment Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    if (mysqli_num_rows($appointmentQuery) > 0):
                        while ($appointment = mysqli_fetch_assoc($appointmentQuery)):
                    ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                            <td><?= htmlspecialchars($appointment['appointment_time']) ?></td>
                            <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                            <td><?= htmlspecialchars($appointment['status']) ?></td>
                            <td><?= htmlspecialchars($appointment['payment_status']) ?></td>
                        </tr>
                    <?php 
                        endwhile;
                    else: 
                    ?>
                        <tr>
                            <td colspan="6" class="text-muted">No completed and paid appointments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
