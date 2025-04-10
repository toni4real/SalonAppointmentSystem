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

    // Ensure end time is after start time
    if ($start_time >= $end_time) {
        $error_message = "End time must be after start time.";
    } else {
        // Insert schedule using prepared statement
        $stmt = $conn->prepare("INSERT INTO staff_scheduling (staff_id, available_date, start_time, end_time) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $staff_id, $available_date, $start_time, $end_time);

        if ($stmt->execute()) {
            header("Location: staff_schedule.php");
            exit();
        } else {
            $error_message = "There was an error adding the schedule.";
        }

        $stmt->close();
    }
}

// Fetch staff members
$staff_stmt = $conn->prepare("SELECT staff_id, name FROM staff WHERE status = 'active'");
$staff_stmt->execute();
$staff_result = $staff_stmt->get_result();

// Fetch staff schedules
$schedule_stmt = $conn->prepare("
    SELECT s.schedule_id, s.available_date, s.start_time, s.end_time, st.name AS staff_name
    FROM staff_scheduling s
    JOIN staff st ON s.staff_id = st.staff_id
    ORDER BY s.available_date DESC
");
$schedule_stmt->execute();
$schedule_result = $schedule_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff Schedules</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/staff_schedule.css">
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">Salon Admin Panel</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Back to Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_appointments.php">Manage Appointments</a></li>
                <li class="nav-item"><a class="nav-link" href="payment_history.php">View Payment Records</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container staff-schedules-container">
    <h2>Manage Staff Schedules</h2>

    <!-- Display error message if any -->
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <!-- Button to trigger modal -->
    <button type="button" class="btn mb-3" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
        Add Schedule
    </button>

    <!-- Add Schedule Modal -->
    <div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addScheduleModalLabel">Add Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="staff_id" class="form-label">Staff Member:</label>
                            <select name="staff_id" class="form-select" required>
                                <?php while ($staff = $staff_result->fetch_assoc()): ?>
                                    <option value="<?php echo $staff['staff_id']; ?>"><?php echo htmlspecialchars($staff['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="available_date" class="form-label">Date:</label>
                            <input type="date" name="available_date" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="start_time" class="form-label">Start Time:</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="end_time" class="form-label">End Time:</label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>

                        <div class="modal-footer justify-content-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn">Add Schedule</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">Current Schedules</h3>
    <div class="staff-schedules-table table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Staff</th>
                    <th>Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($schedule = $schedule_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($schedule['staff_name']); ?></td>
                        <td><?php echo $schedule['available_date']; ?></td>
                        <td><?php echo $schedule['start_time']; ?></td>
                        <td><?php echo $schedule['end_time']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
