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

    // Get appointment, customer info, and service name
    $stmt = $conn->prepare("
        SELECT a.appointment_date, a.customer_id, s.service_name 
        FROM appointments a
        JOIN services s ON a.service_id = s.service_id
        WHERE a.appointment_id = ?
    ");
    $stmt->bind_param("i", $appointmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();

    if ($appointment) {
        $customerId = $appointment['customer_id'];
        $appointmentDate = $appointment['appointment_date'];
        $serviceName = $appointment['service_name'];

        // Mark appointment as completed
        $updateStmt = $conn->prepare("UPDATE appointments SET status = 'Completed' WHERE appointment_id = ?");
        $updateStmt->bind_param("i", $appointmentId);
        if ($updateStmt->execute()) {
            // Insert notification with title and message
            $notifTitle = 'Appointment Completed';
            $message = "Your appointment for $serviceName on " . date('F j, Y', strtotime($appointmentDate)) . " has been marked as completed. Thank you!";
            $notifStmt = $conn->prepare("INSERT INTO notifications (customer_id, service_name, message) VALUES (?, ?, ?)");
            $notifStmt->bind_param("iss", $customerId, $notifTitle, $message);
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
