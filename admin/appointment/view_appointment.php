<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_GET['id'])) {
    die('Appointment ID not provided.');
}

$appointment_id = $_GET['id'];

$query = "
    SELECT 
        a.*, 
        c.first_name, 
        c.last_name, 
        s.service_name,
        s.price,
        p.payment_method,
        p.amount AS payment_amount,
        p.payment_date AS payment_date
    FROM appointments a
    JOIN customers c ON a.customer_id = c.customer_id
    JOIN services s ON a.service_id = s.service_id
    LEFT JOIN payments p ON a.appointment_id = p.appointment_id
    WHERE a.appointment_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();

$admin_id = $_SESSION['admin_id'];

$stmt = $conn->prepare("SELECT first_name FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result_admin = $stmt->get_result();
$adminData = $result_admin->fetch_assoc();

if ($adminData) {
    $firstName = explode(' ', $adminData['first_name'])[0];
} else {
    $firstName = "Admin";
}

if (!$appointment) {
    die('Appointment not found.');
}

// Function to display status badges
function formatStatusBadge($status) {
    switch ($status) {
        case 'Pending':
            return '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Pending</span>';
        case 'Confirmed':
            return '<span class="badge bg-primary"><i class="bi bi-check-circle"></i> Confirmed</span>';
        case 'Completed':
            return '<span class="badge bg-success"><i class="bi bi-patch-check"></i> Completed</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}

// Function to display payment status badge
function formatPaymentStatusBadge($status) {
    if ($status === 'Paid') {
        return '<span class="badge bg-success"><i class="bi bi-credit-card-2-back"></i> Paid</span>';
    } elseif ($status === 'Unpaid') {
        return '<span class="badge bg-danger"><i class="bi bi-credit-card-2-back"></i> Unpaid</span>';
    } else {
        return '<span class="badge bg-secondary">No Payment Info</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../admin/css/admin_appointments.css">
</head>
<body>

<div class="sidebar d-flex flex-column">
    <h4 class="text-white mb-4">Hi, <?= htmlspecialchars($firstName) ?> <span class="wave">👋</span></h4>
    <a class="nav-link <?= ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>" href="../admin_dashboard.php">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a class="nav-link <?= ($current_page == 'admin_profile.php') ? 'active' : ''; ?>" href="../admin_profile.php">
        <i class="bi bi-person-circle"></i> Profile
    </a>
    <a class="nav-link <?= ($current_page == 'admin_appointments.php' || $current_page == 'view_appointment.php') ? 'active' : ''; ?>" href="../admin_appointments.php">
        <i class="bi bi-calendar-check"></i> Appointments
    </a>
    <a class="nav-link <?= ($current_page == 'payment_history.php') ? 'active' : ''; ?>" href="../payment_history.php">
        <i class="bi bi-credit-card-2-front"></i> Payments
    </a>
    <a class="nav-link <?= ($current_page == 'staff_attendance.php') ? 'active' : ''; ?>" href="../staff_attendance.php">
        <i class="bi bi-person-gear"></i> Staff Attendance
    </a>
    <a class="nav-link <?= ($current_page == 'services_list.php') ? 'active' : ''; ?>" href="../services_list.php">
        <i class="bi bi-stars"></i> Services
    </a>
    <a class="nav-link btn btn-danger mt-auto text-white" href="admin_logout.php">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

<div class="main-content">

    <a href="../admin_appointments.php" class="btn btn-secondary mb-4">
        <i class="bi bi-arrow-left"></i> Back to Appointments
    </a>

    <div class="card shadow-sm mb-4">
        <div class="card-header text-white">
            <h4 class="m-0"><i class="bi bi-calendar-event"></i> Appointment Details</h4>
        </div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <strong><i class="bi bi-person"></i> Customer Name:</strong> 
                    <?= htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>
                </li>
                <li class="list-group-item">
                    <strong><i class="bi bi-stars"></i> Service:</strong> 
                    <?= htmlspecialchars($appointment['service_name']); ?> 
                    (PHP <?= number_format($appointment['price'], 2); ?>)
                </li>
                <li class="list-group-item">
                    <strong><i class="bi bi-calendar"></i> Date:</strong> 
                    <?= htmlspecialchars(date('F j, Y', strtotime($appointment['appointment_date']))); ?>
                </li>
                <li class="list-group-item">
                    <strong><i class="bi bi-clock"></i> Time:</strong> 
                    <?= htmlspecialchars(date('g:i A', strtotime($appointment['appointment_time']))); ?>
                </li>
                <li class="list-group-item">
                    <strong><i class="bi bi-info-circle"></i> Status:</strong> 
                    <?= formatStatusBadge($appointment['status']); ?>
                </li>
            </ul>
        </div>
    </div>

    <?php if (isset($appointment['payment_status'])): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header text-white">
            <h4 class="m-0"><i class="bi bi-credit-card-2-back"></i> Payment Details</h4>
        </div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <strong><i class="bi bi-cash-coin"></i> Amount Paid:</strong> 
                    PHP <?= number_format($appointment['payment_amount'], 2); ?>
                </li>
                <li class="list-group-item">
                    <strong><i class="bi bi-calendar-check"></i> Payment Date:</strong> 
                    <?= htmlspecialchars(date('F j, Y \a\t h:i A', strtotime($appointment['payment_date']))); ?>
                </li>
                <li class="list-group-item">
                    <strong><i class="bi bi-credit-card-2-back"></i> Payment Status:</strong> 
                    <?= formatPaymentStatusBadge($appointment['payment_status']); ?>
                </li>
            </ul>
        </div>
    </div>
    <?php else: ?>
    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white">
            <h4 class="m-0"><i class="bi bi-credit-card-2-back"></i> Payment Details</h4>
        </div>
        <div class="card-body">
            <p>No payment information available for this appointment.</p>
        </div>
    </div>
    <?php endif; ?>

</div>

</body>
</html>
