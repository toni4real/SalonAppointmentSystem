<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../customer_login.php");
    exit();
}

if (isset($_GET['id'])) {
    $appointment_id = intval($_GET['id']);

    // Get appointment and service info
    $stmt = $conn->prepare("
        SELECT a.customer_id, a.appointment_date, a.appointment_time, s.service_name 
        FROM appointments a 
        JOIN services s ON a.service_id = s.service_id 
        WHERE a.appointment_id = ? AND a.status = 'Confirmed'
    ");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();

    if ($appointment) {
        $customerId = $appointment['customer_id'];
        $serviceName = $appointment['service_name'];
        $date = $appointment['appointment_date'];
        $time = $appointment['appointment_time'];

        // Cancel the appointment
        $update = $conn->prepare("UPDATE appointments SET status = 'Cancelled' WHERE appointment_id = ?");
        $update->bind_param("i", $appointment_id);
        $update->execute();

        // Insert notification
        $notifTitle = "Appointment Cancelled";
        $message = "Your appointment for $serviceName on $date at $time has been cancelled.";
        $insertNotif = $conn->prepare("INSERT INTO notifications (customer_id, service_name, message) VALUES (?, ?, ?)");
        $insertNotif->bind_param("iss", $customerId, $notifTitle, $message);
        $insertNotif->execute();

        // Fetch the customer's name first
        $getCustomer = $conn->prepare("SELECT first_name, last_name FROM customers WHERE customer_id = ?");
        $getCustomer->bind_param("i", $customerId);
        $getCustomer->execute();
        $customerResult = $getCustomer->get_result()->fetch_assoc();

        $customerFirstName = $customerResult['first_name'];
        $customerLastName = $customerResult['last_name'];
        $customerFullName = $customerFirstName . ' ' . $customerLastName;

        // Prepare the notification
        $notifTitle = "Appointment Cancelled";
        $customerMessage = "$customerFullName has cancelled their appointment for $serviceName on $date at $time.";

        // Insert the notification into admin_notifications
        $insertCustomerNotif = $conn->prepare("INSERT INTO admin_notifications (customer_id, service_name, message) VALUES (?, ?, ?)");
        $insertCustomerNotif->bind_param("iss", $customerId, $notifTitle, $customerMessage);
        $insertCustomerNotif->execute();

        
    }
}

header("Location: ../customer_dashboard.php");
exit();
?>
