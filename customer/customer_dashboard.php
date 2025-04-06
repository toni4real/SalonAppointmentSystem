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
    <style>
/* General body styles */
body {
    background: #F6DEF6; /* Light pink background */
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Fade-in effect for the entire dashboard container */
.dashboard-container {
    padding: 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(255, 8, 8, 0.08);
    margin-top: 2rem; /* Added margin to give some space from the top */
    opacity: 0;
    animation: fadeIn 1s forwards; /* Animation to fade in */
}

@keyframes fadeIn {
    to {
        opacity: 1;
    }
}

/* Navbar styling for the top bar */
.navbar {
    background: #f77fbe; /* Pink background */
    padding: 1rem 0; /* Adds padding for height */
    transition: background-color 0.3s ease; /* Smooth transition for hover effect */
}

.navbar:hover {
    background-color: #e65aa9; /* Darker pink on hover */
}

.navbar-brand {
    color: white !important;
    font-size: 1.5rem; /* Adjust font size */
    text-align: center;
    width: 100%;
    transition: color 0.3s ease;
}

.navbar-brand:hover {
    color: #F6DEF6; /* Change brand color on hover */
}

/* Navbar links styling for the bottom bar */
.navbar-links {
    background: #f77fbe; /* Pink background */
    padding: 1rem;
    border-radius: 8px;
    margin-top: 2rem; /* Add space between "Welcome" and navbar */
    width: 60%; /* Reduced width of the bar */
    margin-left: auto; /* Center the navbar container */
    margin-right: auto; /* Center the navbar container */
}

/* Flex container to arrange navbar items horizontally */
.navbar-nav {
    display: flex;
    justify-content: center; /* Align items in the center */
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-item {
    margin: 0 1rem; /* Space between links */
}

/* Navbar link hover animation */
.nav-link {
    color: white !important; /* White text */
    font-size: 1.1rem;
    text-decoration: none;
    padding: 0.5rem 1.2rem; /* Adjust padding to make the width smaller */
    transition: transform 0.3s ease, background-color 0.3s ease, opacity 0.3s ease; /* Add smooth transform */
    opacity: 0.8; /* Set initial opacity */
}

.nav-link:hover {
    text-decoration: underline;
    transform: scale(1.1); /* Slightly enlarge on hover */
    background-color: rgba(255, 255, 255, 0.1); /* Add subtle background change */
    opacity: 1; /* Full opacity on hover */
}

/* Table container styles */
.table-container {
    margin-top: 2rem;
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(255, 8, 8, 0.08);
    opacity: 0;
    animation: fadeIn 1s forwards; /* Animation to fade in */
}

.table th {
    background-color: #f77fbe; /* Bright pink background for header */
    color: white; /* White text in table header */
    transition: background-color 0.3s ease; /* Smooth transition for header color */
}

.table th:hover {
    background-color: #e65aa9; /* Darker pink on hover */
}

.table td {
    background-color: #F6DEF6; /* Light pink for table cells */
    color: #333; /* Darker text for readability */
    transition: background-color 0.3s ease; /* Smooth transition for row color */
}

.table tbody tr:hover {
    background-color: #e65aa9; /* Darker pink on hover */
    transform: scale(1.02); /* Slightly enlarge row on hover */
    transition: transform 0.3s ease; /* Smooth scaling */
}

.table thead th {
    border-bottom: 2px solid #fff;
}

.table tbody td {
    border-bottom: 1px solid #ddd;
}

.table-hover tbody tr:hover {
    background-color: #e65aa9; /* Darker pink on hover */
}

    </style>
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
