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
        // Get customer_id from appointment
        $getCustomer = $conn->prepare("SELECT customer_id FROM appointments WHERE appointment_id = ?");
        $getCustomer->bind_param("i", $appointmentId);
        $getCustomer->execute();
        $getCustomer->bind_result($customerId);
        $getCustomer->fetch();
        $getCustomer->close();

        // Insert notification
        $message = "Your appointment has been confirmed by the admin.";
        $notif = $conn->prepare("INSERT INTO notifications (customer_id, message) VALUES (?, ?)");
        $notif->bind_param("is", $customerId, $message);
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
