<?php
session_start();
date_default_timezone_set('Asia/Manila'); // <-- Set to your local timezone
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Query to fetch appointments and related data
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

// Check if query was successful
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

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
    <a class="nav-link <?php echo ($current_page == 'staff_attendance.php') ? 'active' : ''; ?>" href="staff_attendance.php">
        <i class="bi bi-person-gear"></i> Staff Attendance
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
                    <th>Appointment ID</th>
                    <th>Customer Name</th>
                    <th>Service</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($appointment = mysqli_fetch_assoc($result)) { 
                    $appointmentDate = new DateTime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']);
                    $appointmentDateFormatted = $appointmentDate->format('F j, Y \a\t g:i A'); // Format: Month Day, Year Hour:Minute AM/PM
                ?>
                    <tr>
                        <td><?= htmlspecialchars($appointment['appointment_id']); ?></td>
                        <td><?= htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></td>
                        <td><?= htmlspecialchars($appointment['service_name']); ?></td>
                        <td><?= htmlspecialchars($appointmentDateFormatted); ?></td>
                        <td><?= htmlspecialchars($appointment['status']); ?></td>
                        <td>
                            <a href="appointment/view_appointment.php?id=<?= $appointment['appointment_id']; ?>" class="btn btn-sm">View</a>
                            <?php if ($appointment['status'] === 'Pending') { ?>
                                <a href="appointment/confirm_appointment.php?id=<?= $appointment['appointment_id']; ?>" class="btn btn-sm btn-outline-success">Confirm</a>
                            <?php } ?>
                            <?php if ($appointment['status'] === 'Confirmed') { ?>
                                <a href="appointment/complete_appointment.php?id=<?= $appointment['appointment_id']; ?>" class="btn btn-sm btn-success">Complete</a>
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
