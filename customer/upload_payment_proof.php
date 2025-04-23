<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

// Ensure only customers can access this page
if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Fetch customer's pending appointments
$query = "SELECT appointment_id, appointment_date, appointment_time FROM appointments WHERE customer_id = ? AND payment_status = 'Unpaid'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$appointments = $stmt->get_result();

// Handle payment proof upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['proof'])) {
    $appointment_id = $_POST['appointment_id'];

    $uploadDir = '../admin/uploads/';
    $fileName = basename($_FILES['proof']['name']);
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['proof']['tmp_name'], $filePath)) {
        $updateQuery = "INSERT INTO payments (appointment_id, amount, payment_method, proof_of_payment) VALUES (?, 0, 'Cash', ?)";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("is", $appointment_id, $fileName);
        $stmt->execute();

        $statusUpdate = "UPDATE appointments SET payment_status = 'Paid' WHERE appointment_id = ?";
        $stmt = $conn->prepare($statusUpdate);
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();

        echo "Payment proof uploaded successfully.";
    } else {
        echo "Error uploading file.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Payment Proof</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: #f8f9fa;
            display: flex;
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
            padding: 30px;
            width: 100%;
        }

        h2 {
            color: #f77fbe;
            text-align: center;
            margin-bottom: 20px;
        }

        .form-container {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: auto;
        }

        label {
            font-weight: bold;
            margin-top: 15px;
        }

        select,
        input[type="file"] {
            margin-top: 5px;
            margin-bottom: 20px;
        }

        button {
            background-color: #f77fbe;
            border: none;
            color: white;
            padding: 10px 20px;
            font-weight: bold;
            border-radius: 0.375rem;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #e063a3;
        }

        p {
            text-align: center;
            color: #dc3545;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h5 class="fw-bold mb-4">Salon Customer Panel</h5>
    <a class="nav-link" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a>
    <a class="nav-link" href="customer_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a class="nav-link" href="appointment_booking.php"><i class="bi bi-calendar-plus-fill"></i> Book Appointment</a>
    <a class="nav-link active" href="upload_payment_proof.php"><i class="bi bi-upload"></i> Upload Payment Proof</a>
    <a class="nav-link" href="appointment_history.php"><i class="bi bi-clock-history"></i> Appointment History</a>
    <a class="nav-link" href="notifications.php"><i class="bi bi-bell"></i> Notifications</a>
    <a class="nav-link" href="help.php"><i class="bi bi-question-circle"></i> Help</a>
    <a class="btn btn-danger text-white" href="customer_logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<div class="main-content">
    <h2>Upload Payment Proof</h2>
    <div class="form-container">
        <?php
        // Sample logic - assumes $appointments is available
        if (isset($appointments) && $appointments->num_rows > 0): ?>
            <form method="POST" action="" enctype="multipart/form-data">
                <label for="appointment_id">Select Appointment:</label>
                <select class="form-select" name="appointment_id" required>
                    <?php while ($row = $appointments->fetch_assoc()): ?>
                        <option value="<?php echo $row['appointment_id']; ?>">
                            <?php echo $row['appointment_date'] . ' ' . $row['appointment_time']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label for="proof">Upload Proof (Image/PDF):</label>
                <input type="file" class="form-control" name="proof" accept="image/*,application/pdf" required>

                <div class="text-center mt-4">
                    <button type="submit">Upload Proof</button>
                </div>
            </form>
        <?php else: ?>
            <p>No pending payments to upload proof for.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
