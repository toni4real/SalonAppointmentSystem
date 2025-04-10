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
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/customer_dashboard.css">
</head>
<body>

    <!-- Navbar with Salon Customer Panel title, centered -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid justify-content-center">
            <a class="navbar-brand fw-bold" href="#">SALON CUSTOMER PANEL</a>
        </div>
    </nav>

    <!-- Centered Welcome Message -->
    <div class="container dashboard-container">
        <div class="welcome-message text-center">
            <h2>Welcome, <?php echo htmlspecialchars($customer['name']); ?>!</h2>
        </div>

        <!-- Navigation Bar with Book Appointment, Upload Payment Proof, and Logout -->
        <div class="navbar-links">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="appointment_booking.php">Book Appointment</a></li>
                <li class="nav-item"><a class="nav-link" href="upload_payment_proof.php">Upload Payment Proof</a></li>
                <li class="nav-item"><a class="nav-link" href="customer_logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Appointments Table -->
        <div class="table-container">
            <h4 class="mb-3">Your Appointments</h4>
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-dark">
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
            </div>
        </div>
    </div>

</body>
</html>
