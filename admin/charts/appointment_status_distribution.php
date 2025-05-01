<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

$sql = "
    SELECT status, COUNT(*) AS total 
    FROM appointments 
    GROUP BY status
";

$result = mysqli_query($conn, $sql);

$labels = [];
$totals = [];

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = ucfirst($row['status']);
    $totals[] = (int)$row['total'];
}

echo json_encode(['labels' => $labels, 'data' => $totals]);
?>
