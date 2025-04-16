<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $staff_id = $_POST['staff_id'];
    $available_date = $_POST['available_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    if ($start_time >= $end_time) {
        $error_message = "End time must be after start time.";
    } else {
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

$staff_stmt = $conn->prepare("SELECT staff_id, name FROM staff WHERE status = 'active'");
$staff_stmt->execute();
$staff_result = $staff_stmt->get_result();

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/staff_schedule.css">
</head>
<body>

<div class="sidebar d-flex flex-column">
    <h4 class="text-white mb-4">Salon Admin</h4>
    <a class="nav-link <?php echo ($current_page == 'admin_profile.php') ? 'active' : ''; ?>" href="admin_profile.php">
        <i class="bi bi-person-circle"></i> Profile
    </a>
    <a class="nav-link <?php echo ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>" href="admin_dashboard.php">
        <i class="bi bi-speedometer2"></i> Dashboard
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

<div class="main-content">
    <h2>Manage Staff Schedules</h2>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <button type="button" class="btn mb-3" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
        Add Schedule
    </button>

    <!-- Modal -->
    <div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addScheduleModalLabel">Add Schedule</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
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
                    </div>
                    <div class="modal-footer justify-content-end">
                        <button type="button" class="btn cancel-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn">Add Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <h4>Current Schedules</h4>
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
