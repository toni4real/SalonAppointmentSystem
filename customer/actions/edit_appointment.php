<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: ../customer_login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No appointment selected.";
    header("Location: ../customer_dashboard.php");
    exit();
}

$appointment_id = $_GET['id'];

// Fetch appointment details
$query = "
    SELECT a.*, s.service_name, s.price
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    WHERE a.appointment_id = $appointment_id AND a.customer_id = $customer_id
";
$result = mysqli_query($conn, $query);
$appointment = mysqli_fetch_assoc($result);

if (!$appointment) {
    $_SESSION['error'] = "Appointment not found.";
    header("Location: ../customer_dashboard.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_id = $_POST['service_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    if ($appointment_date < date('Y-m-d')) {
        $_SESSION['error'] = "Appointment date cannot be in the past.";
        header("Location: edit_appointment.php?id=$appointment_id");
        exit();
    }

    if ($appointment_time < '09:00' || $appointment_time > '18:00') {
        $_SESSION['error'] = "Time must be between 09:00 and 18:00.";
        header("Location: edit_appointment.php?id=$appointment_id");
        exit();
    }

    $update = "
        UPDATE appointments
        SET service_id = $service_id, appointment_date = '$appointment_date', appointment_time = '$appointment_time'
        WHERE appointment_id = $appointment_id AND customer_id = $customer_id
    ";
    if (mysqli_query($conn, $update)) {
        $_SESSION['message'] = "Appointment updated successfully.";
        header("Location: ../customer_dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Update failed.";
    }
}

$services = mysqli_query($conn, "SELECT service_id, service_name, price FROM services");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Appointment</title>
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
        <a class="nav-link" href="../profile.php"><i class="bi bi-person-circle"></i> Profile</a>
        <a class="nav-link active" href="../customer_dashboard.php"><i class="bi bi-speedometer2"></i> Edit Appointments</a>
        <a class="nav-link" href="../appointment_booking.php"><i class="bi bi-calendar-plus-fill"></i> Book Appointment</a>
        <a class="nav-link" href="../customer_history.php"><i class="bi bi-clock-history"></i> Appointment History</a>
        <a class="nav-link" href="../notifications.php"><i class="bi bi-bell"></i> Notifications</a>
        <a class="nav-link" href="../help.php"><i class="bi bi-question-circle"></i> Help</a>
        <a class="btn btn-danger text-white" href="../customer_logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>

    <div class="main-content">
        <div class="container">
            <h2>Edit Appointment</h2>
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Select Service:</label>
                    <select name="service_id" class="form-select" required>
                        <?php while ($service = mysqli_fetch_assoc($services)): ?>
                            <option value="<?php echo $service['service_id']; ?>"
                                <?php echo ($appointment['service_id'] == $service['service_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($service['service_name']) . " - PHP " . $service['price']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date:</label>
                    <input type="date" name="appointment_date" class="form-control" required
                           min="<?php echo date('Y-m-d'); ?>"
                           value="<?php echo $appointment['appointment_date']; ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Time:</label>
                    <input type="time" name="appointment_time" class="form-control" required
                           min="09:00" max="18:00"
                           value="<?php echo $appointment['appointment_time']; ?>">
                </div>
                <button type="submit" class="btn btn-primary">Update Appointment</button>
            </form>
        </div>
    </div>
</body>
</html>
