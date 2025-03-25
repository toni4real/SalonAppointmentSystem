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
    <title>Upload Payment Proof</title>
</head>

<body>
    <h2>Upload Payment Proof</h2>
    <a href="customer_dashboard.php">Back to Dashboard</a>

    <?php if ($appointments->num_rows > 0): ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <label for="appointment_id">Select Appointment:</label>
            <select name="appointment_id" required>
                <?php while ($row = $appointments->fetch_assoc()): ?>
                    <option value="<?php echo $row['appointment_id']; ?>">
                        <?php echo $row['appointment_date'] . ' ' . $row['appointment_time']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="proof">Upload Proof (Image/PDF):</label>
            <input type="file" name="proof" accept="image/*,application/pdf" required>

            <button type="submit">Upload Proof</button>
        </form>
    <?php else: ?>
        <p>No pending payments to upload proof for.</p>
    <?php endif; ?>
</body>

</html>
