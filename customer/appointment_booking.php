<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];
$serviceQuery = mysqli_query($conn, "SELECT service_id, service_name, price FROM services");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_id = $_POST['service_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $current_date = date('Y-m-d');
    $current_time = date('H:i');

    if ($appointment_date < $current_date) {
        $_SESSION['error'] = "Appointment date cannot be in the past.";
        header("Location: appointment_booking.php");
        exit();
    }

    if ($appointment_date == $current_date && $appointment_time <= $current_time) {
        $_SESSION['error'] = "Appointment time must be in the future.";
        header("Location: appointment_booking.php");
        exit();
    }

    if ($appointment_time < '09:00' || $appointment_time > '18:00') {
        $_SESSION['error'] = "Appointment time must be between 09:00 AM and 06:00 PM.";
        header("Location: appointment_booking.php");
        exit();
    }

    $checkExistingService = "
        SELECT 1 
        FROM appointments a
        JOIN services s ON a.service_id = s.service_id
        WHERE a.customer_id = $customer_id
        AND s.service_name = (
            SELECT service_name 
            FROM services 
            WHERE service_id = $service_id
        )
        AND (
            a.status != 'Completed' OR a.payment_status != 'Paid'
        )
        AND a.status != 'Cancelled'
    ";

    $result = mysqli_query($conn, $checkExistingService);
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "You already have this service booked and it is not yet completed or paid.";
        header("Location: appointment_booking.php");
        exit();
    }

    $serviceResult = mysqli_query($conn, "SELECT price FROM services WHERE service_id = $service_id");
    $service = mysqli_fetch_assoc($serviceResult);
    $price = $service['price'];

    $dailyLimit = 10;

    $checkLimit = mysqli_query($conn, "
        SELECT COUNT(*) AS total_appointments
        FROM appointments
        WHERE appointment_date = '$appointment_date'
        AND status != 'Cancelled'
    ");

    $limitData = mysqli_fetch_assoc($checkLimit);
    if ($limitData['total_appointments'] >= $dailyLimit) {
        $_SESSION['error'] = "Sorry, the maximum number of appointments for this day has been reached. Please choose another date.";
        header("Location: appointment_booking.php");
        exit();
    }

    $staffQuery = mysqli_query($conn, "
        SELECT s.staff_id, COUNT(a.appointment_id) AS appointment_count
        FROM staff s
        LEFT JOIN appointments a ON s.staff_id = a.staff_id
            AND a.appointment_date = '$appointment_date' AND a.appointment_time = '$appointment_time'
        WHERE s.status = 'active'
        GROUP BY s.staff_id
        ORDER BY appointment_count ASC, RAND()
        LIMIT 1
    ");

    $staff = mysqli_fetch_assoc($staffQuery);
    if ($staff) {
        $staff_id = $staff['staff_id'];

        $insertAppointment = "INSERT INTO appointments (customer_id, staff_id, service_id, appointment_date, appointment_time, status, booking_type)
                              VALUES (?, ?, ?, ?, ?, 'pending', 'online')";
        $stmt = mysqli_prepare($conn, $insertAppointment);
        mysqli_stmt_bind_param($stmt, 'iiiss', $customer_id, $staff_id, $service_id, $appointment_date, $appointment_time);

        if (mysqli_stmt_execute($stmt)) {
            $appointment_id = mysqli_insert_id($conn);

            $payment_method = 'Unpaid';
            $payment_date = date('Y-m-d H:i:s');
            $insertPayment = "INSERT INTO payments (appointment_id, payment_method, amount, payment_date)
                              VALUES (?, ?, ?, ?)";
            $stmtPayment = mysqli_prepare($conn, $insertPayment);
            mysqli_stmt_bind_param($stmtPayment, 'isds', $appointment_id, $payment_method, $price, $payment_date);
            mysqli_stmt_execute($stmtPayment);

            $getCustomer = $conn->prepare("SELECT first_name, last_name FROM customers WHERE customer_id = ?");
            $getCustomer->bind_param("i", $customer_id);
            $getCustomer->execute();
            $customerResult = $getCustomer->get_result()->fetch_assoc();

            $customerFullName = $customerResult['first_name'] . ' ' . $customerResult['last_name'];

            $notifTitle = "New Appointment Booked";
            $customerMessage = "{$customerFullName} has booked a new appointment on {$appointment_date} at {$appointment_time}.";

            $insertCustomerNotif = $conn->prepare("INSERT INTO admin_notifications (customer_id, service_name, message) VALUES (?, ?, ?)");
            $insertCustomerNotif->bind_param("iss", $customer_id, $notifTitle, $customerMessage);
            $insertCustomerNotif->execute();

            $_SESSION['message'] = "Appointment booked successfully.";
            header("Location: appointment_booking.php");
            exit();
        } else {
            $_SESSION['error'] = "Error booking appointment: " . mysqli_error($conn);
            header("Location: appointment_booking.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "No available staff at the selected time.";
        header("Location: appointment_booking.php");
        exit();
    }
}

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
    <title>Book Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../customer/css/appointment_booking.css">
</head>
<body>
<div class="sidebar">
    <h5 class="fw-bold mb-4">Salon Customer Panel</h5>
    <a class="nav-link" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a>
    <a class="nav-link" href="customer_dashboard.php"><i class="bi bi-speedometer2"></i> Your Appointments</a>
    <a class="nav-link active" href="appointment_booking.php"><i class="bi bi-calendar-plus-fill"></i> Book Appointment</a>
    <a class="nav-link" href="customer_history.php"><i class="bi bi-clock-history"></i> Appointment History</a>

    <a class="nav-link position-relative" href="notifications.php">
        <i class="bi bi-bell"></i> Notifications
        <?php if ($unreadCount > 0): ?>
            <span class="position-absolute top-50 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $unreadCount ?>
            </span>
        <?php endif; ?>
    </a>

    <a class="nav-link" href="help.php"><i class="bi bi-question-circle"></i> Help</a>
    <a class="btn btn-danger text-white" href="customer_logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<div class="main-content">
    <div class="container">
        <h2 class="text-center">Book an Appointment</h2>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form id="appointmentForm" action="" method="POST">
            <div class="mb-3">
                <label for="service_id" class="form-label">Select Service:</label>
                <select name="service_id" class="form-select" required>
                    <?php while ($service = mysqli_fetch_assoc($serviceQuery)): ?>
                        <option value="<?php echo $service['service_id']; ?>">
                            <?php echo htmlspecialchars($service['service_name']) . " - PHP " . $service['price']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="appointment_date" class="form-label">Date:</label>
                <input type="date" name="appointment_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="mb-3">
                <label for="appointment_time" class="form-label">Time:</label>
                <input type="time" name="appointment_time" class="form-control" required min="09:00" max="18:00">
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmationModal">
                Book Appointment
            </button>
        </form>

        <h4 class="mt-5">Current Appointments (Including Walk-Ins)</h4>

        <?php
        $query = "
            SELECT a.appointment_date, a.appointment_time, 
                   c.first_name AS customer_first, c.last_name AS customer_last,
                   a.booking_type
            FROM appointments a
            JOIN customers c ON a.customer_id = c.customer_id
            WHERE a.status != 'Cancelled'
            ORDER BY a.appointment_date, a.appointment_time
        ";
        $result = mysqli_query($conn, $query);
        ?>

        <div class="table-responsive">
            <table class="table table-bordered table-sm text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Customer</th>
                        <th>Booking Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= date('F d, Y', strtotime($row['appointment_date'])) ?></td>
                            <td><?= date('h:i A', strtotime($row['appointment_time'])) ?></td>
                            <td><?= htmlspecialchars($row['customer_first'] . ' ' . $row['customer_last']) ?></td>
                            <td>
                                <?php if ($row['booking_type'] === 'walk-in'): ?>
                                    <span class="badge bg-success">Walk-in</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">Online</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No appointments booked yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmationModalLabel">Confirm Your Appointment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to book this appointment?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <!-- Confirm button triggers form submit -->
        <button type="button" class="btn btn-primary" onclick="document.getElementById('appointmentForm').submit();">
          Confirm
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
