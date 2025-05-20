<?php
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';


$serviceFilter = isset($_GET['service']) ? $_GET['service'] : null;

$labels = [];
$data = [];

if ($serviceFilter) {
    // Filtered data for selected service
    $stmt = $conn->prepare("
        SELECT service_name, COUNT(*) AS usage_count
        FROM appointments a
        JOIN services s ON a.service_id = s.service_id
        WHERE s.service_name = ?
        GROUP BY s.service_name
    ");
    $stmt->bind_param("s", $serviceFilter);
} else {
    // All services
    $stmt = $conn->prepare("
        SELECT service_name, COUNT(*) AS usage_count
        FROM appointments a
        JOIN services s ON a.service_id = s.service_id
        GROUP BY s.service_name
    ");
}

$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $labels[] = $row['service_name'];
    $data[] = $row['usage_count'];
}

// Fetch all services for dropdown
$serviceList = [];
$serviceResult = $conn->query("SELECT DISTINCT service_name FROM services ORDER BY service_name ASC");
while ($row = $serviceResult->fetch_assoc()) {
    $serviceList[] = $row['service_name'];
}

echo json_encode([
    'labels' => $labels,
    'data' => $data,
    'services' => $serviceList
]);

?>
