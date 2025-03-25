<?php
session_start();
require_once 'includes/db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Salon Appointment System</title>
</head>
<body>
    <h1>Welcome to the Salon Appointment System</h1>

    <h2>Login as:</h2>
    <ul>
        <li><a href="admin/admin_login.php">Admin</a></li>
        <li><a href="customer/customer_login.php">Customer</a></li>
    </ul>

    <h2>About Our Services</h2>
    <p>We offer a variety of salon services including hair, nail, spa, and makeup treatments. Book an appointment today!</p>
</body>
</html>
