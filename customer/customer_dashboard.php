<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

// Ensure customer is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Fetch customer details
$customerQuery = mysqli_query($conn, "SELECT * FROM customers WHERE customer_id = '$customer_id'");
$customer = mysqli_fetch_assoc($customerQuery);

// Fetch customer appointments
$appointmentQuery = mysqli_query($conn, "SELECT a.*, s.service_name, st.name AS staff_name 
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    JOIN staff st ON a.staff_id = st.staff_id
    WHERE a.customer_id = '$customer_id'
    ORDER BY a.appointment_date DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Customer Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($customer['name']); ?></h2>

    <nav>
        <a href="appointment_booking.php">Book Appointment</a>
        <a href="upload_payment_proof.php">Upload Payment Proof</a>
        <a href="customer_logout.php">Logout</a>
    </nav>

    <h3>Your Appointments</h3>
    <table border="1">
        <thead>
            <tr>
                <th>Service</th>
                <th>Staff</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Payment Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($appointment = mysqli_fetch_assoc($appointmentQuery)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                    <td><?php echo htmlspecialchars($appointment['staff_name']); ?></td>
                    <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                    <td><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                    <td><?php echo htmlspecialchars($appointment['status']); ?></td>
                    <td><?php echo htmlspecialchars($appointment['payment_status']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
