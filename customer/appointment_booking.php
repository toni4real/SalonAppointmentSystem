<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

$serviceQuery = mysqli_query($conn, "SELECT service_id, service_name, price FROM services");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_SESSION['customer_id'];
    $service_id = $_POST['service_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    // Get service price
    $serviceResult = mysqli_query($conn, "SELECT price FROM services WHERE service_id = $service_id");
    $service = mysqli_fetch_assoc($serviceResult);
    $price = $service['price'];

    // Find available staff
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

        // Insert the appointment into the appointments table
        $insertAppointment = "INSERT INTO appointments (customer_id, staff_id, service_id, appointment_date, appointment_time, status)
                              VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = mysqli_prepare($conn, $insertAppointment);
        mysqli_stmt_bind_param($stmt, 'iiiss', $customer_id, $staff_id, $service_id, $appointment_date, $appointment_time);

        if (mysqli_stmt_execute($stmt)) {
            $appointment_id = mysqli_insert_id($conn);

            // Insert into the payments table
            $payment_method = 'Unpaid'; // Assuming the payment method is "Unpaid" for now
            $payment_date = date('Y-m-d H:i:s'); // Current date and time
            $insertPayment = "INSERT INTO payments (appointment_id, payment_method, amount, payment_date)
                              VALUES (?, ?, ?, ?)";
            $stmtPayment = mysqli_prepare($conn, $insertPayment);
            mysqli_stmt_bind_param($stmtPayment, 'isds', $appointment_id, $payment_method, $price, $payment_date);
            mysqli_stmt_execute($stmtPayment);

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
?>

<!-- HTML UI Code Below Stays the Same -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            display: flex;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            width: 250px;
            background-color: #f77fbe;
            height: 100vh;
            position: fixed;
            padding: 20px 0;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sidebar .nav-link {
            color: white;
            padding: 12px 20px;
            width: 100%;
            text-align: left;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
        .sidebar .btn-danger {
            margin-top: auto;
            margin-bottom: 20px;
            width: 80%;
            border-radius: 20px;
        }
        .main-content {
            margin-left: 250px;
            padding: 40px;
            width: 100%;
        }
        .container {
            max-width: 600px;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.15);
            margin: auto;
        }
        h2 {
            color: #f77fbe;
            margin-bottom: 25px;
            text-align: center;
        }
        .form-label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
            color: #f77fbe;
        }
        .form-select,
        .form-control {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-primary {
            background-color: #f77fbe;
            border-color: #f77fbe;
            color: white;
            display: block;
            margin: 20px auto 0;
            padding: 0.8rem 2rem;
            border-radius: 6px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #e063a3;
            border-color: #e063a3;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h5 class="fw-bold mb-4">Salon Customer Panel</h5>
        <a class="nav-link" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a>
        <a class="nav-link" href="customer_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a class="nav-link active" href="appointment_booking.php"><i class="bi bi-calendar-plus-fill"></i> Book Appointment</a>
        <a class="nav-link" href="customer_history.php"><i class="bi bi-clock-history"></i> Appointment History</a>
        <a class="nav-link" href="notifications.php"><i class="bi bi-bell"></i> Notifications</a>
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
            <form action="" method="POST">
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
                    <input type="date" name="appointment_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="appointment_time" class="form-label">Time:</label>
                    <input type="time" name="appointment_time" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary"
                    onclick="this.disabled=true; this.innerText='Booking...'; this.form.submit();">
                    Book Appointment
                </button>
            </form>
        </div>
    </div>
</body>
</html>
