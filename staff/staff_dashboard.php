<?php
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

session_start();
if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}

$staff_id = $_SESSION['staff_id'];

// Get staff first and last name
$stmt = $conn->prepare("SELECT first_name, last_name FROM staff WHERE staff_id = ?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name);
$stmt->fetch();
$staff_name = $first_name . ' ' . $last_name;
$stmt->close();

// Get upcoming appointments including today
$today = date('Y-m-d');
$query = "
    SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.status,
           CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
           s.service_name
    FROM appointments a
    JOIN customers c ON a.customer_id = c.customer_id
    JOIN services s ON a.service_id = s.service_id
    WHERE a.staff_id = ? AND a.appointment_date >= ?
    ORDER BY a.appointment_date, a.appointment_time
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/staff_dashboard1.css">
</head>
<body>
<div class="dashboard-container">
    <div class="sidebar">
        <div class="staffpanel">Staff Panel</div>
        <a href="staff_dashboard.php">
            <button class="navs text-decoration-none bi-calendar-check"> Appointments</button>
        </a>
        <a href="staff_history.php">
            <button class="navs bi-clock-history"> View History</button>
        </a>
        <a href="staff_profile.php">
            <button class="navs bi-person-gear"> View Profile</button>
        </a>
        <a href="staff_logout.php">
            <button class="logout bi-box-arrow-right"> Logout</button>
        </a>
    </div>

    <div class="main-content">
        <div class="appointment">Appointments</div>
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
                                    <!-- Future: Add Accept/Reject buttons here -->
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if ($result->num_rows === 0): ?>
                            <tr><td colspan="6">No upcoming appointments.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
