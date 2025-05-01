<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

$data = array_fill(1, 12, 0); // Fill all months with zero

$sql = "SELECT MONTH(appointment_date) as month, COUNT(*) as total 
        FROM appointments 
        GROUP BY MONTH(appointment_date)";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $data[(int)$row['month']] = (int)$row['total'];
}

echo json_encode(array_values($data));
?>
