<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Apply ucfirst(strtolower(trim())) to names
    $first_name = ucfirst(strtolower(trim($first_name)));
    $last_name = ucfirst(strtolower(trim($last_name)));

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = "UPDATE customers SET 
                      first_name='$first_name', 
                      last_name='$last_name', 
                      email='$email', 
                      phone='$phone', 
                      password='$hashed' 
                   WHERE customer_id='$customer_id'";
    } else {
        $update = "UPDATE customers SET 
                      first_name='$first_name', 
                      last_name='$last_name', 
                      email='$email', 
                      phone='$phone' 
                   WHERE customer_id='$customer_id'";
    }

    mysqli_query($conn, $update);
    $message = "Profile updated successfully!";
}




$query = mysqli_query($conn, "SELECT * FROM customers WHERE customer_id='$customer_id'");
$customer = mysqli_fetch_assoc($query);

$unreadCount = 0;

if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
    $result = mysqli_query($conn, "
        SELECT COUNT(*) AS unread_count 
        FROM notifications 
        WHERE customer_id = $customer_id AND is_read = 0
    ");
    if ($result) {
        $data = mysqli_fetch_assoc($result);
        $unreadCount = $data['unread_count'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/customer_profile.css">
</head>

<body>

    <div class="sidebar">
        <h5 class="fw-bold mb-4">Salon Customer Panel</h5>
        <a class="nav-link active" href="profile.php"><i class="bi bi-person-circle"></i> Edit Profile</a>
        <a class="nav-link" href="customer_dashboard.php"><i class="bi bi-speedometer2"></i> Your Appointments</a>
        <a class="nav-link" href="appointment_booking.php"><i class="bi bi-calendar-plus-fill"></i> Book Appointment</a>
        <a class="nav-link" href="customer_history.php"><i class="bi bi-clock-history"></i> Appointment History</a>
        
        <a class="nav-link position-relative" href="notifications.php">
        <i class="bi bi-bell"></i> Notifications
        <?php if ($unreadCount > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $unreadCount ?>
            </span>
        <?php endif; ?>
        </a>


        <a class="nav-link" href="help.php"><i class="bi bi-question-circle"></i> Help</a>
        <a class="btn btn-danger text-white" href="customer_logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>

    <div class="main-content">
        <div class="container">
            <h3>Edit Profile</h3>
            <?php if (!empty($message)): ?>
                <div class="alert alert-success"><?= $message ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($customer['first_name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($customer['last_name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">email</label>
                    <input type="text" name="email" class="form-control" value="<?= htmlspecialchars($customer['email']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($customer['phone']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password (leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>

</body>

</html>