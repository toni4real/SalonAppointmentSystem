<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: staff_schedule.php');
    exit();
}

$schedule_id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM staff_scheduling WHERE schedule_id = ?");
$stmt->bind_param("i", $schedule_id);

if ($stmt->execute()) {
    header("Location: staff_schedule.php");
    exit();
} else {
    echo "Error deleting schedule.";
}

$stmt->close();
?>
