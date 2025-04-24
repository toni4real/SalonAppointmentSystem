<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Validate and sanitize appointment ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $appointment_id = intval($_GET['id']);

    // Update status to "Completed"
    $stmt = $conn->prepare("UPDATE appointments SET status = 'completed' WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointment_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Appointment marked as completed.";
    } else {
        $_SESSION['error'] = "Failed to complete appointment.";
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid appointment ID.";
}

header("Location: ../admin_appointments.php");
exit();
?>
