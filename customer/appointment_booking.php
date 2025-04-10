<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

// Ensure only logged-in customers can access this page
if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

// Fetch available services
$serviceQuery = mysqli_query($conn, "SELECT service_id, service_name, price FROM services");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_SESSION['customer_id'];
    $service_id = $_POST['service_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    // Fetch an available staff for the given time and date
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

        $insertQuery = "INSERT INTO appointments (customer_id, staff_id, service_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($stmt, 'iiiss', $customer_id, $staff_id, $service_id, $appointment_date, $appointment_time);

        if (mysqli_stmt_execute($stmt)) {
            echo "Appointment booked successfully.";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "No available staff at the selected time. Please choose a different time.";
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
