<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

// Initialize revenue for 12 months
$monthlyRevenue = array_fill(0, 12, 0);

// Modify the query to join the appointments table with the payments table
$sql = "
    SELECT MONTH(a.appointment_date) AS month, SUM(p.amount) AS totalRevenue
    FROM appointments a
    INNER JOIN payments p ON a.appointment_id = p.appointment_id
    WHERE a.payment_status = 'paid'
    GROUP BY MONTH(a.appointment_date)
";

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $monthIndex = (int)$row['month'] - 1;
    $monthlyRevenue[$monthIndex] = (float)$row['totalRevenue'];
}

echo json_encode($monthlyRevenue);
?>
