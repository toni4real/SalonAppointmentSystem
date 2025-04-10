<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

// Ensure only admins can access this page
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Database query error handling
function getCount($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    if (!$result) {
        die('Query failed: ' . mysqli_error($conn));
    }
    return mysqli_fetch_assoc($result)['total'];
}

// Fetch total counts
$customerCount = getCount("SELECT COUNT(*) as total FROM customers");
$staffCount = getCount("SELECT COUNT(*) as total FROM staff");
$appointmentCount = getCount("SELECT COUNT(*) as total FROM appointments");
$pendingPayments = getCount("SELECT COUNT(*) as total FROM payments WHERE proof_of_payment = ''");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Salon System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/admin_dashboard.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">Salon Admin Panel</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="admin_appointments.php">Manage Appointments</a></li>
                    <li class="nav-item"><a class="nav-link" href="payment_history.php">View Payment Records</a></li>
                    <li class="nav-item"><a class="nav-link" href="staff_schedule.php">Manage Staff Schedules</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container dashboard mt-5">
        <h2 class="mb-4">Welcome to Admin Dashboard</h2>
        <div class="row g-4 justify-content-center">
            <div class="col-md-3 col-sm-6">
                <div class="card text-center p-4">
                    <i class="bi bi-people-fill"></i>
                    <h5 class="mt-2">Total Customers</h5>
                    <h2><?php echo $customerCount; ?></h2>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card text-center p-4">
                    <i class="bi bi-person-badge-fill"></i>
                    <h5 class="mt-2">Total Staff</h5>
                    <h2><?php echo $staffCount; ?></h2>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card text-center p-4">
                    <i class="bi bi-calendar-check-fill"></i>
                    <h5 class="mt-2">Total Appointments</h5>
                    <h2><?php echo $appointmentCount; ?></h2>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card text-center p-4">
                    <i class="bi bi-cash-stack"></i>
                    <h5 class="mt-2">Pending Payments</h5>
                    <h2><?php echo $pendingPayments; ?></h2>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
