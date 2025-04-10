<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

// Ensure only admins can access this page
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Fetch appointments from the database
$query = "SELECT * FROM appointments";
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Appointments - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/admin_appointments.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">Salon Admin Panel</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Back to Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="payment_history.php">View Payment Records</a></li>
                    <li class="nav-item"><a class="nav-link" href="staff_schedule.php">Manage Staff Schedules</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container appointments-container">
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
