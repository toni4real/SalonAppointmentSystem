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
    <title>Staff Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/staff_profile.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="staffpanel">Staff Panel </div>
            <a href="staff_dashboard.php">
                <button class="navs text-decoration-none bi-calendar-check"> Appointments</button>
            </a>

            <a href="staff_history.php">
                <button class="navs bi-clock-history"> View History</button>
            </a>
            <a href="staff_profile.php">
                <button class="navs bi-person-gear"> View Profile</button>
            </a>
            <a href="staff_logout.php">
                <button class="logout bi-box-arrow-right" > Logout</button>
            </a>
            </div>
        
        <div class="main-content">
      <div class="staffprofile">Staff Profile</div>

      <div class="profile-card">
        <?php if (isset($success)): ?>
          <div class="alert alert-success"><?= $success ?></div>
        <?php elseif (isset($error)): ?>
          <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
          <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($admin['name']) ?>" required>
          </div>

          <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <input type="text" class="form-control" name="role" value="<?= htmlspecialchars($admin['email']) ?>" required>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Phone Number</label>
            <input type="number" class="form-control" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
          </div>

          <div class="mb-3">
            <label for="new_password" class="form-label">Password <small class="text-muted">(leave blank to keep current)</small></label>
            <input type="password" class="form-control" name="new_password">
          </div>


          <div class="d-flex justify-content-end">
            <a href="admin_dashboard.php" class="btn cancel-btn">Cancel</a>
            <button type="submit" class="btn">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
        


    
</body>
</html>