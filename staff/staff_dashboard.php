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

<div class="dashboard-container">
    <div class="card">
        <div class="header">
            <h3>Welcome, <?php echo htmlspecialchars($staff_name); ?> 👋</h3>
            <a href="staff_logout.php" class="btn btn-light btn-sm float-end">Logout</a>
        </div>
        <div class="card-body">
            <h5>Today's Appointments (<?php echo $today; ?>)</h5>
            <?php if ($result->num_rows > 0): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                            <td><span class="badge bg-<?php 
                                echo match ($row['status']) {
                                    'pending' => 'warning',
                                    'confirmed' => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'secondary',
                                };
                            ?>"><?php echo ucfirst($row['status']); ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">You have no appointments today.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
