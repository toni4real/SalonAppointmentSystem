<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

// Ensure only logged-in customers can access this page
if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

// Fetch available staff and services
$staffQuery = mysqli_query($conn, "SELECT staff_id, name FROM staff WHERE status = 'active'");
$serviceQuery = mysqli_query($conn, "SELECT service_id, service_name, price FROM services");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_SESSION['customer_id'];
    $staff_id = $_POST['staff_id'];
    $service_id = $_POST['service_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    $insertQuery = "INSERT INTO appointments (customer_id, staff_id, service_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?, 'pending')";
    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, 'iiiss', $customer_id, $staff_id, $service_id, $appointment_date, $appointment_time);

    if (mysqli_stmt_execute($stmt)) {
        echo "Appointment booked successfully.";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../css/appointment_booking.css">
    <title>Book Appointment</title>
</head>

<body>
    <h2>Book an Appointment</h2>

    <form action="" method="POST">
        <label for="staff_id">Select Staff:</label>
        <select name="staff_id" required>
            <?php while ($staff = mysqli_fetch_assoc($staffQuery)): ?>
                <option value="<?php echo $staff['staff_id']; ?>"> <?php echo htmlspecialchars($staff['name']); ?> </option>
            <?php endwhile; ?>
        </select><br>

        <label for="service_id">Select Service:</label>
        <select name="service_id" required>
            <?php while ($service = mysqli_fetch_assoc($serviceQuery)): ?>
                <option value="<?php echo $service['service_id']; ?>">
                    <?php echo htmlspecialchars($service['service_name']) . " - PHP " . $service['price']; ?>
                </option>
            <?php endwhile; ?>
        </select><br>

        <label for="appointment_date">Date:</label>
        <input type="date" name="appointment_date" required><br>

        <label for="appointment_time">Time:</label>
        <input type="time" name="appointment_time" required><br>

        <button type="submit">Book Appointment</button>
    </form>

    <a href="customer_dashboard.php">Back to Dashboard</a>
</body>
</html>
