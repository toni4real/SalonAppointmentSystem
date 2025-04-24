<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

// Ensure only admins can access this page
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Get the appointment ID from the query string
if (isset($_GET['id'])) {
    $appointmentId = $_GET['id'];

    // Update the status of the appointment to 'Confirmed'
    $stmt = $conn->prepare("UPDATE appointments SET status = 'Confirmed' WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointmentId);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Appointment confirmed successfully!";
    } else {
        $_SESSION['error'] = "Failed to confirm the appointment.";
    }

    // Redirect back to the admin appointments page
    header("Location: ../admin_appointments.php");
    exit();
} else {
    // If no ID is passed, redirect to the appointments page
    header("Location: ../admin_appointments.php");
    exit();
}
?>
