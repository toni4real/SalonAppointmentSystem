<?php
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

session_start();
if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}

$staff_id = $_SESSION['staff_id'];

// Get staff profile details
$stmt = $conn->prepare("SELECT first_name, last_name, role, phone, email FROM staff WHERE staff_id = ?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $role, $phone, $email);
$stmt->fetch();
$stmt->close();

$full_name = $first_name . ' ' . $last_name;
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
            <div class="staffpanel">Staff Panel</div>
            <a href="staff_dashboard.php"><button class="navs bi-calendar-check"> Appointments</button></a>
            <a href="staff_history.php"><button class="navs bi-clock-history"> View History</button></a>
            <a href="staff_profile.php"><button class="navs bi-person-gear"> View Profile</button></a>
            <a href="staff_logout.php"><button class="logout bi-box-arrow-right"> Logout</button></a>
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
                        <label for="full_name" class="form-label">Name</label>
                        <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($full_name) ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <input type="text" class="form-control" name="role" value="<?= htmlspecialchars($role) ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($phone) ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>" disabled>
                    </div>

                    <!-- You can enable password change functionality later -->
                    <!--
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" name="new_password">
                    </div>
                    -->

                    <div class="d-flex justify-content-end">
                        <a href="staff_dashboard.php" class="btn cancel-btn">Back</a>
                        <!--
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        -->
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
