<?php
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

session_start();
if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}

$staff_id = $_SESSION['staff_id'];

// Get staff name
$stmt = $conn->prepare("SELECT name FROM staff WHERE staff_id = ?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$stmt->bind_result($staff_name);
$stmt->fetch();
$stmt->close();

// Get today's appointments
$today = date('Y-m-d');
$query = "
    SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.status,
           c.name AS customer_name, s.service_name
    FROM appointments a
    JOIN customers c ON a.customer_id = c.customer_id
    JOIN services s ON a.service_id = s.service_id
    WHERE a.staff_id = ? AND a.appointment_date = ?
    ORDER BY a.appointment_time
";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $staff_id, $today);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/staff_dashboard.css">
</head>
<body>

<div class="navbar">STAFF PANEL</div>
<div class="container">
    <div class="navbar-container">
        <div class="appointment">Appointments</div>
        <button class="history"><a href="staff_history.php" class="text-decoration-none text-white">History</a></button>
        <button class="logout"><a href="staff_logout.php" class="text-decoration-none text-white">Logout</a></button>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Accept/Reject</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($appointment = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($appointment['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['status']); ?></td>
                            <td>
                                <!-- You can add accept/reject buttons or links here -->
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>