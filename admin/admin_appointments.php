<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

// Ensure only admins can access this page
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Fetch all appointments
$query = "
    SELECT a.appointment_id, c.name AS customer_name, s.name AS staff_name, sr.service_name, a.appointment_date, a.appointment_time, a.status
    FROM appointments a
    JOIN customers c ON a.customer_id = c.customer_id
    JOIN staff s ON a.staff_id = s.staff_id
    JOIN services sr ON a.service_id = sr.service_id
    ORDER BY a.appointment_date DESC
";
$result = mysqli_query($conn, $query);

// Update appointment status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['appointment_id'], $_POST['status'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $updateQuery = "UPDATE appointments SET status = '$status' WHERE appointment_id = $appointment_id";
    mysqli_query($conn, $updateQuery);
    header('Location: admin_appointments.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Appointments</title>
</head>
<body>
    <h2>Manage Appointments</h2>
    <a href="admin_dashboard.php">Back to Dashboard</a>

    <table border="1">
        <thead>
            <tr>
                <th>Appointment ID</th>
                <th>Customer</th>
                <th>Staff</th>
                <th>Service</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['appointment_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['staff_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                    <td><?php echo $row['appointment_date']; ?></td>
                    <td><?php echo $row['appointment_time']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                            <select name="status">
                                <option value="pending" <?php echo $row['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $row['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="completed" <?php echo $row['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $row['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
