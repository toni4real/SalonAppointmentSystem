<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $appointmentId = intval($_GET['id']);

    // Confirm appointment
    $stmt = $conn->prepare("UPDATE appointments SET status = 'Confirmed' WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointmentId);

    if ($stmt->execute()) {
        // Get customer_id and service_name
        $query = "
            SELECT a.customer_id, s.service_name
            FROM appointments a
            JOIN services s ON a.service_id = s.service_id
            WHERE a.appointment_id = ?
        ";
        $getDetails = $conn->prepare($query);
        $getDetails->bind_param("i", $appointmentId);
        $getDetails->execute();
        $getDetails->bind_result($customerId, $serviceName);
        $getDetails->fetch();
        $getDetails->close();

        // Insert notification with service_name as title
        $notifTitle = 'Appointment Confirmation';
        $message = "Your appointment for $serviceName has been confirmed. Please arrive on time!";
        $notif = $conn->prepare("INSERT INTO notifications (customer_id, service_name, message) VALUES (?, ?, ?)");
        $notif->bind_param("iss", $customerId, $notifTitle, $message);
        $notif->execute();
        $notif->close();

        $_SESSION['message'] = "Appointment confirmed successfully!";
    } else {
        $_SESSION['error'] = "Failed to confirm the appointment.";
    }

    header("Location: ../admin_appointments.php");
    exit();
} else {
    header("Location: ../admin_appointments.php");
    exit();
}
?>
