<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admins SET first_name = ?, last_name = ?, email = ?, password = ? WHERE admin_id = ?");
        $stmt->bind_param("ssssi", $first_name, $last_name, $email, $hashed_password, $admin_id);
    } else {
        $stmt = $conn->prepare("UPDATE admins SET first_name = ?, last_name = ?, email = ? WHERE admin_id = ?");
        $stmt->bind_param("sssi", $first_name, $last_name, $email, $admin_id);
    }

    $success = $stmt->execute() ? "Profile updated successfully." : "Failed to update profile.";
    $stmt->close();
}

$stmt = $conn->prepare("SELECT first_name, last_name, email, phone FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

$firstName = explode(' ', $admin['first_name'])[0];

// Updated unread notification count query
$unreadCount = 0;

if ($admin_id) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS unread_count
        FROM admin_notifications
        WHERE is_read = 0
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $unreadCount = $data['unread_count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../admin/css/admin_profile.css">
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
        <?php if ($unreadCount > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $unreadCount ?>
            </span>
        <?php endif; ?>
    </a>
    <a class="nav-link <?= ($current_page == 'admin_help.php') ? 'active' : ''; ?>" href="admin_help.php">
      <i class="bi bi-question-circle"></i> Help
    </a>
    <a class="nav-link btn btn-danger mt-auto text-white" href="admin_logout.php">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

<div class="main-content">
  <h2>Admin Profile</h2>
  <hr>
  <?php if (isset($success)): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php elseif (isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>
  <div class="profile-card">
    <form method="POST" action="">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="first_name" class="form-label">First Name</label>
          <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($admin['first_name']) ?>" required>
        </div>
        <div class="col-md-6 mb-3">
          <label for="last_name" class="form-label">Last Name</label>
          <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($admin['last_name']) ?>" required>
        </div>
      </div>

      <div class="mb-3">
        <label for="phone" class="form-label">Phone Number</label>
        <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($admin['phone']) ?>" required>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
      </div>

      <div class="mb-3">
        <label for="new_password" class="form-label">New Password <small class="text-muted">(leave blank to keep current)</small></label>
        <input type="password" class="form-control" name="new_password">
      </div>

      <div class="d-flex justify-content-end">
        <button type="submit" class="btn save-btn"> 
          <i class="bi bi-check-circle me-1"></i> Save Changes
        </button>
        <a href="admin_dashboard.php" class="btn cancel-btn">Cancel</a>
      </div>
    </form>
  </div>
</div>

</body>
</html>
