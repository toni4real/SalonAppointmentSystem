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

// Fetch appointments
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
    ORDER BY 
        a.appointment_date DESC, a.appointment_time DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Appointment History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/customer_history.css">
    <style>
        .sidebar a.nav-link {
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h5 class="fw-bold mb-4">Salon Customer Panel</h5>
    <a class="nav-link" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a>
    <a class="nav-link" href="customer_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a class="nav-link" href="appointment_booking.php"><i class="bi bi-calendar-plus-fill"></i> Book Appointment</a>
    <a class="nav-link active" href="customer_history.php"><i class="bi bi-clock-history"></i> Appointment History</a>
    <a class="nav-link" href="notifications.php"><i class="bi bi-bell"></i> Notifications</a>
    <a class="nav-link" href="help.php"><i class="bi bi-question-circle"></i> Help</a>
    <a class="btn btn-danger text-white" href="customer_logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<div class="main-content">
    <div class="welcome-message text-center mb-4">
        <h2>Welcome To Appointment History</h2>
    </div>

    <div class="table-container">
        <h4>Your History History</h4>
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
                    $counter = 1; // Start counting from 1
                    while ($appointment = mysqli_fetch_assoc($appointmentQuery)): 
                    ?>
                        <tr>
                            <td><?php echo $counter++; ?></td> <!-- Display and increment -->
                            <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['status']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['payment_status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>

            </table>
        </div>
    </div>
</div>

</body>
</html>
