<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

date_default_timezone_set('Asia/Manila');

$current_page = basename($_SERVER['PHP_SELF']);

$firstName = $_SESSION['admin_first_name'] ?? 'Admin';
$today = date('F j, Y');       // Display on page (ex: April 25, 2025)
$today_db = date('Y-m-d');      // Pass in URL (ex: 2025-04-25)

// Fetch all staff (no filtering by status)
$staffList = mysqli_query($conn, "SELECT staff_id, first_name, last_name FROM staff");

$admin_id = $_SESSION['admin_id'];

// Get Admin first name
$stmt = $conn->prepare("SELECT first_name FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin_result = $stmt->get_result();
$admin_data = $admin_result->fetch_assoc();
$firstName = explode(' ', $admin_data['first_name'])[0];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../admin/css/staff_attendance.css">
</head>
<body>

<div class="sidebar d-flex flex-column">
    <h4 class="text-white mb-4">Hi, <?= htmlspecialchars($firstName) ?> <span class="wave">👋</span></h4>
    <a class="nav-link <?= ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>" href="admin_dashboard.php">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a class="nav-link <?= ($current_page == 'admin_profile.php') ? 'active' : ''; ?>" href="admin_profile.php">
        <i class="bi bi-person-circle"></i> Profile
    </a>
    <a class="nav-link <?= ($current_page == 'admin_appointments.php') ? 'active' : ''; ?>" href="admin_appointments.php">
        <i class="bi bi-calendar-check"></i> Appointments
    </a>
    <a class="nav-link <?= ($current_page == 'payment_history.php') ? 'active' : ''; ?>" href="payment_history.php">
        <i class="bi bi-credit-card-2-front"></i> Payments
    </a>
    <a class="nav-link <?= ($current_page == 'staff_management.php') ? 'active' : ''; ?>" href="staff_management.php">
        <i class="bi bi-person-gear"></i> Staff Management
    </a>
    <a class="nav-link <?= ($current_page == 'staff_attendance.php') ? 'active' : ''; ?>" href="staff_attendance.php">
        <i class="bi bi-person-lines-fill"></i> Staff Attendance
    </a>
    <a class="nav-link <?= ($current_page == 'services_list.php') ? 'active' : ''; ?>" href="services_list.php">
        <i class="bi bi-stars"></i> Services
    </a>
    <a class="nav-link <?= ($current_page == 'notifications.php') ? 'active' : ''; ?>" href="notifications.php">
        <i class="bi bi-bell-fill"></i> Notifications
    </a>
    <a class="nav-link btn btn-danger mt-auto text-white" href="admin_logout.php">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

<div class="main-content">
  <h2>Staff Attendance for <?= $today ?></h2>
  <hr>
  <form method="POST" action="attendance/save_attendance.php">
    <div class="staff-attendance-table table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Present</th>
            <th>Absent</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $counter = 1;
          while ($staff = mysqli_fetch_assoc($staffList)) : ?>
            <tr>
              <td><?= $counter++ ?></td>
              <td><?= htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) ?></td>
              <td>
                <input type="radio" name="attendance[<?= $staff['staff_id'] ?>]" value="Present" onchange="updateStatus(this)">
              </td>
              <td>
                <input type="radio" name="attendance[<?= $staff['staff_id'] ?>]" value="Absent" onchange="updateStatus(this)">
              </td>
              <td id="status-<?= $staff['staff_id'] ?>">-</td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>

      <div class="text-end">
        <button type="submit" class="btn save-btn">
          <i class="bi bi-check-circle me-1"></i> Save Changes
        </button>
      </div>
  </form>

        <?php if (isset($_GET['saved']) && $_GET['saved'] === '1') : ?>
        <div class="text-center mt-3">
        <a href="attendance/generate_attendance.php?date=<?= $today_db ?>" class="btn btn-secondary">
          <i class="bi bi-download"></i> Download Attendance
        </a>
        </div>
        <?php endif; ?>
    </div>
</div>

  <script>
    function updateStatus(input) {
      const staffId = input.name.match(/\d+/)[0];
      const statusCell = document.getElementById('status-' + staffId);
      statusCell.textContent = input.value;
    }
  </script>

</body>
</html>
