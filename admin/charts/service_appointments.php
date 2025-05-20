<?php
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');

$service_id = isset($_GET['service']) ? intval($_GET['service']) : 0;

if (!$service_id) {
    echo json_encode(['success' => false, 'message' => 'Service ID not specified or invalid', 'appointments' => []]);
    exit;
}

$stmt = $conn->prepare("SELECT customers.first_name, customers.last_name, appointments.appointment_date, appointments.appointment_time 
                        FROM appointments 
                        JOIN customers ON appointments.customer_id = customers.customer_id 
                        WHERE appointments.service_id = ? 
                        ORDER BY appointments.appointment_date ASC, appointments.appointment_time ASC");

$stmt->bind_param('i', $service_id);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];

while ($row = $result->fetch_assoc()) {
    $appointments[] = [
        'name' => $row['first_name'] . ' ' . $row['last_name'],
        'date' => $row['appointment_date'],
        'time' => $row['appointment_time']
    ];
}

echo json_encode(['success' => true, 'appointments' => $appointments]);
?>