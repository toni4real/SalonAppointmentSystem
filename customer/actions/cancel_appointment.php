<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

if (isset($_GET['id'])) {
    $appointment_id = intval($_GET['id']);

    // Check if the appointment is actually confirmed before updating
    $checkQuery = mysqli_query($conn, "SELECT * FROM appointments WHERE appointment_id = $appointment_id AND status = 'Confirmed'");
    
    if (mysqli_num_rows($checkQuery) > 0) {
        mysqli_query($conn, "UPDATE appointments SET status = 'Cancelled' WHERE appointment_id = $appointment_id");
    }
}

header("Location: ../customer_dashboard.php");
exit();
