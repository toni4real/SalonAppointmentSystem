<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Add schedule logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_schedule'])) {
    $staff_id = $_POST['staff_id'];
    $available_date = $_POST['available_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    if ($start_time >= $end_time) {
        $error_message = "End time must be after start time.";
    } else {
        $check_stmt = $conn->prepare("SELECT * FROM staff_scheduling WHERE staff_id = ? AND available_date = ? AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))");
        $check_stmt->bind_param("isssss", $staff_id, $available_date, $end_time, $start_time, $start_time, $end_time);
        $check_stmt->execute();
        $conflict_result = $check_stmt->get_result();

        if ($conflict_result->num_rows > 0) {
            $error_message = "Schedule conflict: This staff already has a schedule during this time.";
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
        $check_stmt->close();
    }
}

// Update schedule logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_schedule'])) {
    $schedule_id = $_POST['schedule_id'];
    $staff_id = $_POST['staff_id'];
    $available_date = $_POST['available_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    if ($start_time >= $end_time) {
        $error_message = "End time must be after start time.";
    } else {
        $check_stmt = $conn->prepare("SELECT * FROM staff_scheduling WHERE staff_id = ? AND available_date = ? AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?)) AND schedule_id != ?");
        $check_stmt->bind_param("isssssi", $staff_id, $available_date, $end_time, $start_time, $start_time, $end_time, $schedule_id);
        $check_stmt->execute();
        $conflict_result = $check_stmt->get_result();

        if ($conflict_result->num_rows > 0) {
            $error_message = "Schedule conflict: This staff already has a schedule during this time.";
        } else {
            $stmt = $conn->prepare("UPDATE staff_scheduling SET staff_id = ?, available_date = ?, start_time = ?, end_time = ? WHERE schedule_id = ?");
            $stmt->bind_param("isssi", $staff_id, $available_date, $start_time, $end_time, $schedule_id);
            if ($stmt->execute()) {
                header("Location: staff_schedule.php");
                exit();
            } else {
                $error_message = "Error updating schedule.";
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}

$staff_stmt = $conn->prepare("SELECT staff_id, first_name, last_name FROM staff WHERE status = 'active'");
$staff_stmt->execute();
$staff_result = $staff_stmt->get_result();

$schedule_stmt = $conn->prepare("SELECT s.schedule_id, s.staff_id, s.available_date, s.start_time, s.end_time, CONCAT(st.first_name, ' ', st.last_name) AS staff_name FROM staff_scheduling s JOIN staff st ON s.staff_id = st.staff_id ORDER BY s.available_date DESC");
$schedule_stmt->execute();
$schedule_result = $schedule_stmt->get_result();

$admin_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT first_name FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$adminData = $result->fetch_assoc();
$firstName = explode(' ', $adminData['first_name'])[0];

// Edit form data
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_stmt = $conn->prepare("SELECT * FROM staff_scheduling WHERE schedule_id = ?");
    $edit_stmt->bind_param("i", $edit_id);
    $edit_stmt->execute();
    $edit_result = $edit_stmt->get_result();
    $edit_data = $edit_result->fetch_assoc();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff Schedules</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../admin/css/staff_schedule.css">
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
    <a class="nav-link <?php echo ($current_page == 'services_list.php') ? 'active' : ''; ?>" href="services_list.php">
        <i class="bi bi-stars"></i> Services
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
        <i class="bi bi-plus-circle"></i> Add Schedule
    </button>

    <!-- Modal -->
    <div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addScheduleModalLabel">Add New Schedule</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="staff_id" class="form-label">Staff Member:</label>
                            <select name="staff_id" class="form-select" required>
                                <?php while ($staff = $staff_result->fetch_assoc()): ?>
                                    <option value="" disabled selected>Choose a staff member...</option>
                                    <option value="<?php echo $staff['staff_id']; ?>"><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="available_date" class="form-label">Available Date:</label>
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
                        <button type="submit" class="btn">Add Schedule</button>
                        <button type="button" class="btn cancel-btn" data-bs-dismiss="modal">Cancel</button>
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($schedule = $schedule_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($schedule['staff_name']); ?></td>
                        <td><?php echo $schedule['available_date']; ?></td>
                        <td><?php echo $schedule['start_time']; ?></td>
                        <td><?php echo $schedule['end_time']; ?></td>
                        <td class="d-flex justify-content-start gap-2">
                            <button 
                                type="button"
                                class="btn edit-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#editScheduleModal"
                                data-schedule-id="<?= $schedule['schedule_id'] ?>"
                                data-staff-id="<?= $schedule['staff_id'] ?>"
                                data-date="<?= $schedule['available_date'] ?>"
                                data-start="<?= $schedule['start_time'] ?>"
                                data-end="<?= $schedule['end_time'] ?>"
                            >
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>

                            <button 
                                type="button"
                                class="btn delete-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteScheduleModal"
                                data-schedule-id="<?= $schedule['schedule_id'] ?>"
                            >
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Edit Schedule Modal -->
<div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="update_schedule.php">
        <input type="hidden" name="schedule_id" id="edit_schedule_id">
        <div class="modal-header">
          <h5 class="modal-title">Edit Schedule</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Staff Member</label>
            <select name="staff_id" id="edit_staff_id" class="form-select" required>
              <?php
              $staff_result->data_seek(0); // Reset pointer
              while ($staff = $staff_result->fetch_assoc()):
              ?>
                <option value="<?= $staff['staff_id'] ?>"><?= htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" name="available_date" id="edit_date" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Start Time</label>
            <input type="time" name="start_time" id="edit_start" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">End Time</label>
            <input type="time" name="end_time" id="edit_end" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn">Update Schedule</button>
            <button type="button" class="btn cancel-btn" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteScheduleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="delete_schedule.php">
        <input type="hidden" name="schedule_id" id="delete_schedule_id">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Deletion</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete this schedule?</p>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn delete-btn">Yes</button>
            <button type="button" class="btn cancel-btn" data-bs-dismiss="modal">No</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const editButtons = document.querySelectorAll('.edit-btn');
  const modal = new bootstrap.Modal(document.getElementById('editScheduleModal'));

  editButtons.forEach(button => {
    button.addEventListener('click', () => {
      document.getElementById('edit_schedule_id').value = button.dataset.scheduleId;
      document.getElementById('edit_staff_id').value = button.dataset.staffId;
      document.getElementById('edit_date').value = button.dataset.date;
      document.getElementById('edit_start').value = button.dataset.start;
      document.getElementById('edit_end').value = button.dataset.end;
    });
  });
});
</script>


</body>
</html>
