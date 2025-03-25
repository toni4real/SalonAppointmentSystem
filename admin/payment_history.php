<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

// Ensure only admins can access this page
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Fetch payment records with related appointment and customer data
$query = "
    SELECT p.payment_id, p.amount, p.payment_method, p.payment_date, p.proof_of_payment,
           a.appointment_id, a.appointment_date, a.appointment_time,
           c.name AS customer_name
    FROM payments p
    JOIN appointments a ON p.appointment_id = a.appointment_id
    JOIN customers c ON a.customer_id = c.customer_id
    ORDER BY p.payment_date DESC
";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Payment History</title>
</head>

<body>
    <h2>Payment History</h2>

    <a href="admin_dashboard.php">Back to Dashboard</a>

    <table border="1">
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Customer</th>
                <th>Appointment Date</th>
                <th>Appointment Time</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Payment Date</th>
                <th>Proof of Payment</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['payment_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td><?php echo $row['appointment_date']; ?></td>
                    <td><?php echo $row['appointment_time']; ?></td>
                    <td><?php echo $row['amount']; ?></td>
                    <td><?php echo $row['payment_method']; ?></td>
                    <td><?php echo $row['payment_date']; ?></td>
                    <td>
                        <?php if ($row['proof_of_payment']): ?>
                            <a href="uploads/<?php echo $row['proof_of_payment']; ?>" target="_blank">View Proof</a>
                        <?php else: ?>
                            No proof uploaded
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>

</html>
