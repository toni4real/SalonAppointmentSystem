<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

// Ensure only admins can access this page
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Handle schedule creation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $staff_id = $_POST['staff_id'];
    $available_date = $_POST['available_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $stmt = $conn->prepare("INSERT INTO staff_scheduling (staff_id, available_date, start_time, end_time) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $staff_id, $available_date, $start_time, $end_time);
    $stmt->execute();
    $stmt->close();

    header("Location: staff_schedule.php");
    exit();
}

// Fetch staff members
$staff_result = mysqli_query($conn, "SELECT staff_id, name FROM staff WHERE status = 'active'");

// Fetch staff schedules
$schedule_result = mysqli_query($conn, "
    SELECT s.schedule_id, s.available_date, s.start_time, s.end_time, st.name AS staff_name
    FROM staff_scheduling s
    JOIN staff st ON s.staff_id = st.staff_id
    ORDER BY s.available_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Staff Schedule Management</title>
</head>

<body>
    <h2>Manage Staff Schedules</h2>

    <a href="admin_dashboard.php">Back to Dashboard</a>

    <h3>Add Schedule</h3>
    <form method="POST" action="">
        <label for="staff_id">Staff Member:</label>
        <select name="staff_id" required>
            <?php while ($staff = mysqli_fetch_assoc($staff_result)): ?>
                <option value="<?php echo $staff['staff_id']; ?>">
                    <?php echo htmlspecialchars($staff['name']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="available_date">Date:</label>
        <input type="date" name="available_date" required>

        <label for="start_time">Start Time:</label>
        <input type="time" name="start_time" required>

        <label for="end_time">End Time:</label>
        <input type="time" name="end_time" required>

        <button type="submit">Add Schedule</button>
    </form>

    <h3>Current Schedules</h3>
    <table border="1">
        <thead>
            <tr>
                <th>Staff</th>
                <th>Date</th>
                <th>Start Time</th>
                <th>End Time</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($schedule = mysqli_fetch_assoc($schedule_result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($schedule['staff_name']); ?></td>
                    <td><?php echo $schedule['available_date']; ?></td>
                    <td><?php echo $schedule['start_time']; ?></td>
                    <td><?php echo $schedule['end_time']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>

</html>
