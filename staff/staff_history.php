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

// Get past appointments (before today)
$today = date('Y-m-d');
$query = "
    SELECT a.appointment_id, a.appointment_date, a.appointment_time, 
           s.price, c.name AS customer_name, s.service_name
    FROM appointments a
    JOIN customers c ON a.customer_id = c.customer_id
    JOIN services s ON a.service_id = s.service_id
    WHERE a.staff_id = ? AND a.appointment_date < ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
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
    <title>Appointment History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/staff_dashboard.css">
</head>
<body>
<div class="dashboard-container">
    <div class="navbar">
        <div class="staffpanel">STAFF PANEL</div>
        <div class="navbarright">
            <a href="staff_dashboard.php">
                <button class="viewappointments bi-calendar-check"> Appointments</button>
            </a>
            <a href="staff_history.php">
                <button class="viewhistory bi-clock-history"> View History</button>
            </a>
            <a href="staff_profile.php">
                <button class="viewprofile bi-person-gear"> View Profile</button>
            </a>
            <a href="staff_logout.php">
                <button class="logout bi-box-arrow-right"> Logout</button>
            </a>
        </div>
    </div>

    <div class="appointment">Appointment History</div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Appointment ID</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['appointment_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                            <td>₱<?php echo number_format($row['price'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>



