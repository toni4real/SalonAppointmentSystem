<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

function getCount($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    if (!$result) {
        die('Query failed: ' . mysqli_error($conn));
    }
    return mysqli_fetch_assoc($result)['total'];
}

$admin_id = $_SESSION['admin_id'];

// Get admin's name
$stmt = $conn->prepare("SELECT first_name FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$adminData = $result->fetch_assoc();

$firstName = explode(' ', $adminData['first_name'])[0]; // Get only the first word of the name

$customerCount = getCount("SELECT COUNT(*) as total FROM customers");
$staffCount = getCount("SELECT COUNT(*) as total FROM staff");
$appointmentCount = getCount("SELECT COUNT(*) as total FROM appointments");
$pendingPayments = getCount("SELECT COUNT(*) as total FROM payments WHERE proof_of_payment = ''");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../css/admin_dashboard.css">
</head>
<body>
  
<div class="sidebar d-flex flex-column">
    <h4 class="text-white mb-4">Hi, <?= htmlspecialchars($firstName) ?> <span class="wave">👋</span></h4>
    <a class="nav-link <?php echo ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>" href="admin_dashboard.php">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a class="nav-link <?php echo ($current_page == 'admin_profile.php') ? 'active' : ''; ?>" href="admin_profile.php">
        <i class="bi bi-person-circle"></i> Profile
    </a>
    <a class="nav-link <?php echo ($current_page == 'admin_appointments.php') ? 'active' : ''; ?>" href="admin_appointments.php">
        <i class="bi bi-calendar-check"></i> Appointments
    </a>
    <a class="nav-link <?php echo ($current_page == 'payment_history.php') ? 'active' : ''; ?>" href="payment_history.php">
        <i class="bi bi-credit-card-2-front"></i> Payments
    </a>
    <a class="nav-link <?php echo ($current_page == 'staff_schedule.php') ? 'active' : ''; ?>" href="staff_schedule.php">
        <i class="bi bi-person-gear"></i> Staff Schedules
    </a>
    <a class="nav-link btn btn-danger mt-auto text-white" href="admin_logout.php">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

<!-- Main content -->
<div class="main-content">
        <h2>Dashboard Overview</h2>
        <div class="dashboard-cards">
            <div class="card">
                <i class="bi bi-people-fill"></i>
                <h5>Total Customers</h5>
                <h2><?php echo $customerCount; ?></h2>
            </div>
            <div class="card">
                <i class="bi bi-person-badge-fill"></i>
                <h5>Total Staff</h5>
                <h2><?php echo $staffCount; ?></h2>
            </div>
            <div class="card">
                <i class="bi bi-calendar-check-fill"></i>
                <h5>Total Appointments</h5>
                <h2><?php echo $appointmentCount; ?></h2>
            </div>
            <div class="card">
                <i class="bi bi-cash-stack"></i>
                <h5>Pending Payments</h5>
                <h2><?php echo $pendingPayments; ?></h2>
            </div>
        </div>
    </div>
</div>

</body>
</html>
