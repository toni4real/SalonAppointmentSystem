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

// Correct SELECT query
$stmt = $conn->prepare("SELECT first_name, last_name, email, phone FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Get only the first name (can also reuse $admin['first_name'])
$firstName = explode(' ', $admin['first_name'])[0];
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../css/admin_profile.css">
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
    <a class="nav-link btn btn-danger mt-auto text-white" href="admin_logout.php">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

    <!-- Content -->
    <div class="main-content">
      <h2>Admin Profile</h2>

      <div class="profile-card">
        <?php if (isset($success)): ?>
          <div class="alert alert-success"><?= $success ?></div>
        <?php elseif (isset($error)): ?>
          <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
          <div class="mb-3">
            <label for="first_name" class="form-label">First Name</label>
            <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($admin['first_name']) ?>" required>
          </div>
          <div class="mb-3">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($admin['last_name']) ?>" required>
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
            <a href="admin_dashboard.php" class="btn cancel-btn">Cancel</a>
            <button type="submit" class="btn">Save Changes</button>
          </div>
        </form>
      </div>
    </div>


</body>
</html>
