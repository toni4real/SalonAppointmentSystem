<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

if (isset($_GET['id'])) {
    $appointment_id = intval($_GET['id']);
    $customer_id = $_SESSION['customer_id'];

    // Ensure the appointment belongs to the logged-in customer
    $checkQuery = mysqli_query($conn, "SELECT * FROM appointments WHERE appointment_id = $appointment_id AND customer_id = $customer_id");
    
    if (mysqli_num_rows($checkQuery) > 0) {
        mysqli_query($conn, "DELETE FROM appointments WHERE appointment_id = $appointment_id");
    }
}

header('Location: ../customer_dashboard.php');
exit();
