<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

// Determine current page for navbar highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Ensure only admins can access this page
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Fetch appointments from the database
$query = "SELECT * FROM appointments";
$result = mysqli_query($conn, $query);

$admin_id = $_SESSION['admin_id'];

// Get admin's name
$stmt = $conn->prepare("SELECT first_name FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$adminData = $result->fetch_assoc();

$firstName = explode(' ', $adminData['first_name'])[0]; // Get only the first word of the name
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/admin_appointments.css">
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

    <!-- Content Area -->
    <div class="main-content">
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
                    <?php while ($appointment = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?php echo $appointment['appointment_id']; ?></td>
                            <td><?php echo $appointment['customer_name']; ?></td>
                            <td><?php echo $appointment['service']; ?></td>
                            <td><?php echo $appointment['appointment_date']; ?> <?php echo $appointment['appointment_time']; ?></td>
                            <td><?php echo $appointment['status']; ?></td>
                            <td>
                                <a href="view_appointment.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                <a href="edit_appointment.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-sm btn-outline-success">Edit</a>
                                <a href="delete_appointment.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-sm btn-outline-danger">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
