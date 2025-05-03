<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Get admin first name
$stmt = $conn->prepare("SELECT first_name FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result_admin = $stmt->get_result();
$adminData = $result_admin->fetch_assoc();
$firstName = !empty($adminData['first_name']) ? explode(' ', $adminData['first_name'])[0] : 'Admin';

// Get unread notification count for this admin
$unreadCount = 0;
if ($admin_id) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS unread_count
        FROM admin_notifications
        WHERE admin_id = ? AND is_read = 0
    ");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $unreadCount = $data['unread_count'] ?? 0;
}

// Handle manual payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_paid_id'])) {
    $paymentId = $_POST['mark_paid_id'];

    $stmt = $conn->prepare("SELECT appointment_id FROM payments WHERE payment_id = ?");
    $stmt->bind_param("i", $paymentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $appointmentId = $row['appointment_id'];

        $updateStmt = $conn->prepare("UPDATE appointments SET payment_status = 'Paid' WHERE appointment_id = ?");
        $updateStmt->bind_param("i", $appointmentId);

        if ($updateStmt->execute()) {
            // Get customer_id from appointment
            $custStmt = $conn->prepare("SELECT customer_id FROM appointments WHERE appointment_id = ?");
            $custStmt->bind_param("i", $appointmentId);
            $custStmt->execute();
            $custStmt->bind_result($customerId);
            $custStmt->fetch();
            $custStmt->close();

            // Insert customer notification
            $serviceName = "Payment";
            $notifMsg = "Your payment has been confirmed. Thank you!";
            $notifStmt = $conn->prepare("INSERT INTO notifications (customer_id, service_name, message) VALUES (?, ?, ?)");
            $notifStmt->bind_param("iss", $customerId, $serviceName, $notifMsg);
            $notifStmt->execute();
            $notifStmt->close();

            $_SESSION['message'] = "Payment marked as paid successfully.";
            header("Location: payment_history.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to mark payment as paid.";
        }
    } else {
        $_SESSION['error'] = "Appointment not found for this payment.";
    }
}

// Fetch payment history
$query = "SELECT p.*, c.first_name, c.last_name, a.payment_status
          FROM payments p
          JOIN appointments a ON p.appointment_id = a.appointment_id
          JOIN customers c ON a.customer_id = c.customer_id
          WHERE a.status != 'Cancelled'";

$result = mysqli_query($conn, $query);
if (!$result) {
    die('Query failed: ' . mysqli_error($conn));
}

// Format badge function
function formatPaymentStatusBadge($status) {
    if ($status === 'Paid') {
        return '<span class="badge bg-success"><i class="bi bi-credit-card-2-back"></i> Paid</span>';
    } elseif ($status === 'Unpaid') {
        return '<span class="badge bg-danger"><i class="bi bi-credit-card-2-back"></i> Unpaid</span>';
    } else {
        return '<span class="badge bg-secondary">No Payment Info</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../admin/css/payment_history.css">
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
    <a class="nav-link position-relative <?= ($current_page == 'notifications.php') ? 'active' : ''; ?>" href="notifications.php">
        <i class="bi bi-bell-fill"></i> Notifications
        <?php if ($unreadCount > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $unreadCount ?>
            </span>
        <?php endif; ?>
    </a>
    <a class="nav-link btn btn-danger mt-auto text-white" href="admin_logout.php">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

<div class="main-content">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <h2>Payment History</h2>
    <div class="payment-history-table table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer Name</th>
                    <th>Amount</th>
                    <th>Payment Date</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th>Receipt</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php $counter = 1; ?>
                    <?php while ($payment = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $counter++; ?></td>
                            <td><?= htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                            <td><?= 'PHP ' . number_format($payment['amount'], 2); ?></td>
                            <td><?= date('F j, Y', strtotime($payment['payment_date'])); ?></td>
                            <td><?= formatPaymentStatusBadge($payment['payment_status']); ?></td>
                            <td>
                                <?php if ($payment['payment_status'] === 'Unpaid'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="mark_paid_id" value="<?= $payment['payment_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-check-circle"></i> Mark as Paid
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-success"><i class="bi bi-check-circle-fill"></i> Confirmed</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($payment['payment_status'] === 'Paid'): ?>
                                    <a href="receipt/generate_receipt.php?payment_id=<?= $payment['payment_id']; ?>" class="btn btn-sm btn-secondary">
                                        <i class="bi bi-download"></i> Download Receipt
                                    </a>
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7">No payment records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
