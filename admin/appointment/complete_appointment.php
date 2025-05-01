<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

if (isset($_GET['id'])) {
    $appointmentId = $_GET['id'];

    // Get appointment and customer info
    $stmt = $conn->prepare("SELECT appointment_date, customer_id FROM appointments WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();

    if ($appointment) {
        $customerId = $appointment['customer_id'];
        $appointmentDate = $appointment['appointment_date'];

        // Mark appointment as completed
        $updateStmt = $conn->prepare("UPDATE appointments SET status = 'Completed' WHERE appointment_id = ?");
        $updateStmt->bind_param("i", $appointmentId);
        if ($updateStmt->execute()) {
            // Insert notification for the customer
            $message = "Your appointment on " . date('F j, Y', strtotime($appointmentDate)) . " has been marked as completed.";
            $notifStmt = $conn->prepare("INSERT INTO notifications (customer_id, message) VALUES (?, ?)");
            $notifStmt->bind_param("is", $customerId, $message);
            $notifStmt->execute();

            $_SESSION['message'] = "Appointment marked as completed and customer notified.";
        } else {
            $_SESSION['error'] = "Failed to mark appointment as completed.";
        }
    } else {
        $_SESSION['error'] = "Appointment not found.";
    }
    header("Location: ../admin_appointments.php");
    exit();
}
?>
