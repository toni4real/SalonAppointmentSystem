<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

$sql = "
    SELECT s.service_name AS service_name, COUNT(a.service_id) AS total
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    GROUP BY a.service_id
    ORDER BY total DESC
    LIMIT 5
";

$result = mysqli_query($conn, $sql);

$labels = [];
$totals = [];

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['service_name'];
    $totals[] = (int)$row['total'];
}

echo json_encode(['labels' => $labels, 'data' => $totals]);
?>
