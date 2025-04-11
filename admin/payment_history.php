<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

// Determine current page for navbar highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Ensure only admins can access this page
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Fetch payment history from the database
$query = "SELECT * FROM payments";
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/payment_history.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">Salon Admin Panel</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>" href="admin_dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'admin_appointments.php') ? 'active' : ''; ?>" href="admin_appointments.php">
                            <i class="bi bi-calendar-check"></i> Manage Appointments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'payment_history.php') ? 'active' : ''; ?>" href="payment_history.php">
                            <i class="bi bi-credit-card-2-front"></i> View Payment Records
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'staff_schedule.php') ? 'active' : ''; ?>" href="staff_schedule.php">
                            <i class="bi bi-person-gear"></i> Manage Staff Schedules
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-danger text-white" href="admin_logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container payment-history-container">
        <h2>Payment History</h2>
        <div class="payment-history-table table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Customer Name</th>
                        <th>Amount</th>
                        <th>Payment Date</th>
                        <th>Proof of Payment</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($payment = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?php echo $payment['payment_id']; ?></td>
                            <td><?php echo $payment['customer_name']; ?></td>
                            <td><?php echo '₱' . number_format($payment['amount'], 2); ?></td>
                            <td><?php echo $payment['payment_date']; ?></td>
                            <td>
                                <?php if ($payment['proof_of_payment'] != '') { ?>
                                    <a href="<?php echo $payment['proof_of_payment']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                <?php } else { ?>
                                    <span>No proof uploaded</span>
                                <?php } ?>
                            </td>
                            <td><?php echo $payment['status']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
