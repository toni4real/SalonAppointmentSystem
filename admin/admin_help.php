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

// Get Admin first name
$stmt = $conn->prepare("SELECT first_name FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin_result = $stmt->get_result();
$admin_data = $admin_result->fetch_assoc();
$firstName = explode(' ', $admin_data['first_name'])[0];

// Get unread notification count for the admin
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

$firstName = explode(' ', $admin_data['first_name'])[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Help</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../admin/css/admin_help.css">
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
    <a class="nav-link <?= ($current_page == 'admin_walkins.php') ? 'active' : ''; ?>" href="admin_walkins.php">
        <i class="bi bi-door-open"></i> Walk-ins
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

    <a class="nav-link <?= ($current_page == 'admin_promos.php') ? 'active' : ''; ?>" href="admin_promos.php">
        <i class="bi bi-tag"></i> Promos
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
    <div class="container">
        <h2>Need Help?</h2>
        <hr>
        <p>If you need assistance or encounter an issue while using the admin dashboard, please contact support:</p>
        <ul>
            <li>Email: <a href="mailto:marketing@theatriumsalons.com">marketing@theatriumsalons.com</a></li>
            <li>Phone: +63 951 784 4284</li>
            <li>Facebook: <a href="https://www.facebook.com/TouchedbyAtriumCastillejos" target="_blank">https://www.facebook.com/TouchedbyAtriumCastillejos</a></li>
        </ul>

        <p>You can also download the full Admin User Guide below:</p>
        <a class="btn-pdf" href="files/admin_user_guide.pdf" target="_blank">
            <i class="bi bi-file-earmark-arrow-down"></i> Download Admin User Guide (PDF)
        </a>
    </div>
</div>

</body>
</html>
