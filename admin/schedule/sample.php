<?php
include '../config/db_connect.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

$sql = "SELECT ss.schedule_id, s.staff_id, s.name AS staff_name, ss.available_date, ss.start_time, ss.end_time 
        FROM staff_schedule ss
        JOIN staff s ON ss.staff_id = s.staff_id
        ORDER BY ss.available_date, ss.start_time";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Schedule</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Staff Schedule</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Staff Name</th>
                <th>Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($schedule = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($schedule['staff_name']) ?></td>
                <td><?= htmlspecialchars($schedule['available_date']) ?></td>
                <td><?= htmlspecialchars($schedule['start_time']) ?></td>
                <td><?= htmlspecialchars($schedule['end_time']) ?></td>
                <td>
                    <button 
                        type="button"
                        class="btn btn-sm btn-primary edit-btn"
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
                        class="btn btn-sm btn-danger delete-btn"
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

<!-- Edit Modal -->
<div class="modal fade" id="editScheduleModal" tabindex="-1" aria-hidden="true">
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
            <label for="edit_date" class="form-label">Date</label>
            <input type="date" class="form-control" name="available_date" id="edit_date" required>
          </div>
          <div class="mb-3">
            <label for="edit_start" class="form-label">Start Time</label>
            <input type="time" class="form-control" name="start_time" id="edit_start" required>
          </div>
          <div class="mb-3">
            <label for="edit_end" class="form-label">End Time</label>
            <input type="time" class="form-control" name="end_time" id="edit_end" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update</button>
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
          <h5 class="modal-title text-danger">Confirm Deletion</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete this schedule?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const editButtons = document.querySelectorAll('.edit-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', () => {
            document.getElementById('edit_schedule_id').value = button.dataset.scheduleId;
            document.getElementById('edit_date').value = button.dataset.date;
            document.getElementById('edit_start').value = button.dataset.start;
            document.getElementById('edit_end').value = button.dataset.end;
        });
    });

    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', () => {
            document.getElementById('delete_schedule_id').value = button.dataset.scheduleId;
        });
    });
});
</script>
</body>
</html>
