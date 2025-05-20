<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once '../includes/db_connection.php';

$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Get admin name for sidebar greeting
$stmt = $conn->prepare("SELECT first_name FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result_admin = $stmt->get_result();
$adminData = $result_admin->fetch_assoc();
$firstName = $adminData ? explode(' ', $adminData['first_name'])[0] : "Admin";

// Fetch unread notifications (if applicable)
$unreadCount = 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $service_id = (int)$_POST['service_id'];
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];

    $status = "Pending";
    $type = "Walk-in";
    $payment_status = "Unpaid";
    $staff_id = NULL;

    // Insert customer
    $insertCustomerQuery = "INSERT INTO customers (first_name, last_name, email, phone) VALUES (?, ?, ?, ?)";
    $stmtCustomer = $conn->prepare($insertCustomerQuery);
    $stmtCustomer->bind_param("ssss", $first_name, $last_name, $email, $phone);
    $stmtCustomer->execute();
    $customer_id = $stmtCustomer->insert_id;

    // Insert appointment
    $insertAppointmentQuery = "INSERT INTO appointments (
        customer_id, staff_id, service_id, appointment_date, appointment_time, status, type, payment_status, admin_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertAppointmentQuery);
    $stmt->bind_param("iiisssssi", $customer_id, $staff_id, $service_id, $date, $time, $status, $type, $payment_status, $admin_id);
    $stmt->execute();

    header("Location: admin_walkins.php");
    exit();
}

// Fetch walk-ins
$query = "
    SELECT a.*, c.first_name, c.last_name, s.service_name
    FROM appointments a
    JOIN customers c ON a.customer_id = c.customer_id
    JOIN services s ON a.service_id = s.service_id
    WHERE a.type = 'Walk-in'
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Walk-in Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="../admin/css/admin_walkins.css" />
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

<!-- MAIN CONTENT -->
<div class="main-content">
    <h2>Walk-in Appointments</h2>
    <hr>
    <div class="walkins-table table-responsive">
        <button class="btn mb-3" data-bs-toggle="modal" data-bs-target="#addWalkinModal">
           <i class="bi bi-plus-circle"></i> Add Walk-in
        </button>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer Name</th>
                    <th>Service</th>
                    <th>Date & Time</th>
                    <th>Added At</th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $count++; ?></td>
                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td><?= htmlspecialchars($row['service_name']); ?></td>
                    <td>
                        <?php 
                            $dt = DateTime::createFromFormat('Y-m-d H:i:s', $row['appointment_date'] . ' ' . $row['appointment_time']);
                            echo $dt ? $dt->format('M d, Y h:i A') : htmlspecialchars($row['appointment_date'] . ' ' . $row['appointment_time']);
                        ?>
                    </td>
                    <td><?= date('M d, Y h:i A'); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Walk-in Modal -->
<div class="modal fade" id="addWalkinModal" tabindex="-1" aria-labelledby="addWalkinModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="admin_walkins.php" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Walk-in Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" name="first_name" id="first_name" class="form-control" required />
                </div>
                <div class="mb-2">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" name="last_name" id="last_name" class="form-control" required />
                </div>
                <div class="mb-2">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required />
                </div>
                <div class="mb-2">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" name="phone" id="phone" class="form-control" required />
                </div>
                <div class="mb-2">
                    <label for="service_id" class="form-label">Service</label>
                    <select name="service_id" id="service_id" class="form-select" required>
                        <?php
                        $serviceResult = mysqli_query($conn, "SELECT service_id, service_name FROM services");
                        while ($service = mysqli_fetch_assoc($serviceResult)) {
                            echo "<option value='{$service['service_id']}'>" . htmlspecialchars($service['service_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label for="appointment_date" class="form-label">Date</label>
                    <input type="date" name="appointment_date" id="appointment_date" class="form-control" required />
                </div>
                <div class="mb-2">
                    <label for="appointment_time" class="form-label">Time</label>
                    <input type="time" name="appointment_time" id="appointment_time" class="form-control" required />
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_walkin" class="btn">Add Walk-in</button>
                <button type="button" class="btn cancel-btn" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
