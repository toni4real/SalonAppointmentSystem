<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Functions for status badges
function formatStatusBadge($status) {
    switch ($status) {
        case 'Pending':
            return '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Pending</span>';
        case 'Confirmed':
            return '<span class="badge bg-primary"><i class="bi bi-check-circle"></i> Confirmed</span>';
        case 'Completed':
            return '<span class="badge bg-success"><i class="bi bi-patch-check"></i> Completed</span>';
        case 'Cancelled':
            return '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Cancelled</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}

// Fetch appointments
$query = "
    SELECT 
        a.*, 
        c.first_name, 
        c.last_name, 
        s.service_name 
    FROM appointments a
    JOIN customers c ON a.customer_id = c.customer_id
    JOIN services s ON a.service_id = s.service_id
";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$admin_id = $_SESSION['admin_id'];

$stmt = $conn->prepare("SELECT first_name FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result_admin = $stmt->get_result();
$adminData = $result_admin->fetch_assoc();

$firstName = $adminData ? explode(' ', $adminData['first_name'])[0] : "Admin";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../admin/css/admin_appointments.css">
</head>
<body>

<div class="sidebar d-flex flex-column">
    <h4 class="text-white mb-4">Hi, <?= htmlspecialchars($firstName) ?> <span class="wave">👋</span></h4>
    <a class="nav-link <?= ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>" href="admin_dashboard.php">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a class="nav-link <?= ($current_page == 'admin_profile.php') ? 'active' : ''; ?>" href="admin_profile.php">
        <i class="bi bi-person-circle"></i> Profile
    </a>
    <a class="nav-link <?= ($current_page == 'admin_appointments.php') ? 'active' : ''; ?>" href="admin_appointments.php">
        <i class="bi bi-calendar-check"></i> Appointments
    </a>
    <a class="nav-link <?= ($current_page == 'payment_history.php') ? 'active' : ''; ?>" href="payment_history.php">
        <i class="bi bi-credit-card-2-front"></i> Payments
    </a>
    <a class="nav-link <?= ($current_page == 'staff_management.php') ? 'active' : ''; ?>" href="staff_management.php">
        <i class="bi bi-person-gear"></i> Staff Management
    </a>
    <a class="nav-link <?= ($current_page == 'staff_attendance.php') ? 'active' : ''; ?>" href="staff_attendance.php">
        <i class="bi bi-person-lines-fill"></i> Staff Attendance
    </a>
    <a class="nav-link <?= ($current_page == 'services_list.php') ? 'active' : ''; ?>" href="services_list.php">
        <i class="bi bi-stars"></i> Services
    </a>
    <a class="nav-link btn btn-danger mt-auto text-white" href="admin_logout.php">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

<div class="main-content">

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

    <h2>Manage Appointments</h2>

    <div class="appointments-table table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer Name</th>
                    <th>Service</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = 1; // Start counting appointments from 1
                while ($appointment = mysqli_fetch_assoc($result)) { 
                    $appointmentDate = new DateTime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']);
                    $appointmentDateFormatted = $appointmentDate->format('F j, Y \a\t g:i A');
                ?>
                    <tr>
                        <td><?= $counter++; ?></td> <!-- Custom appointment ID starting from 1 -->
                        <td><?= htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></td>
                        <td><?= htmlspecialchars($appointment['service_name']); ?></td>
                        <td><?= htmlspecialchars($appointmentDateFormatted); ?></td>
                        <td><?= formatStatusBadge($appointment['status']); ?></td>
                        <td>
                            <a href="appointment/view_appointment.php?id=<?= $appointment['appointment_id']; ?>" class="btn btn-sm complete-btn">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <?php if ($appointment['status'] === 'Pending') { ?>
                                <a href="appointment/confirm_appointment.php?id=<?= $appointment['appointment_id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-check-circle"></i> Confirm
                                </a>
                            <?php } ?>
                            <?php if ($appointment['status'] === 'Confirmed') { ?>
                                <a href="appointment/complete_appointment.php?id=<?= $appointment['appointment_id']; ?>" class="btn btn-sm btn-success">
                                    <i class="bi bi-check2-circle"></i> Complete
                                </a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
