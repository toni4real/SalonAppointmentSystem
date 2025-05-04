<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

if (isset($_GET['id'])) {
    $appointment_id = intval($_GET['id']);

    // Fetch customer_id, service_name, and status before deletion
    $fetchStmt = $conn->prepare("
        SELECT a.customer_id, s.service_name, a.status 
        FROM appointments a 
        JOIN services s ON a.service_id = s.service_id 
        WHERE a.appointment_id = ?
    ");
    $fetchStmt->bind_param("i", $appointment_id);
    $fetchStmt->execute();
    $fetchResult = $fetchStmt->get_result();

    if ($fetchResult && $fetchResult->num_rows > 0) {
        $data = $fetchResult->fetch_assoc();
        $customerId = $data['customer_id'];
        $serviceName = $data['service_name'];
        $status = $data['status'];

        // Delete appointment
        $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
        $stmt->bind_param("i", $appointment_id);

        if ($stmt->execute()) {
            // Only send notification if appointment was not already cancelled
            if (strtolower($status) !== 'cancelled') {
                $notifTitle = 'Appointment Cancelled';
                $message = "Your appointment for $serviceName has been cancelled by the admin.";
                $notif = $conn->prepare("INSERT INTO notifications (customer_id, service_name, message) VALUES (?, ?, ?)");
                $notif->bind_param("iss", $customerId, $notifTitle, $message);
                $notif->execute();
                $notif->close();
            }

            $_SESSION['message'] = "Appointment deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete appointment.";
        }
    } else {
        $_SESSION['error'] = "Appointment not found.";
    }

    $fetchStmt->close();
} else {
    $_SESSION['error'] = "Invalid appointment ID.";
}

header("Location: ../admin_appointments.php");
exit();
