<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

// Ensure only admins can access this page
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Fetch total counts
$customerCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM customers"))['total'];
$staffCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM staff"))['total'];
$appointmentCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM appointments"))['total'];
$pendingPayments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM payments WHERE proof_of_payment = ''"))['total'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h2>Welcome to Admin Dashboard</h2>

    <div>
        <p>Total Customers: <?php echo $customerCount; ?></p>
        <p>Total Staff: <?php echo $staffCount; ?></p>
        <p>Total Appointments: <?php echo $appointmentCount; ?></p>
        <p>Pending Payments: <?php echo $pendingPayments; ?></p>
    </div>

    <nav>
        <a href="admin_appointments.php">Manage Appointments</a>
        <a href="payment_history.php">View Payment Records</a>
        <a href="staff_schedule.php">Manage Staff Schedules</a>
        <a href="admin_logout.php">Logout</a>
    </nav>
</body>
</html>
