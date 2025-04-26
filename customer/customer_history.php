<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];



$query = mysqli_query($conn, "SELECT * FROM customers WHERE customer_id='$customer_id'");
$customer = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/customer_history.css">
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
        
    </div>

</body>

</html>