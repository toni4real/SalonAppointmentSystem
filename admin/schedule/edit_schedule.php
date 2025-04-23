<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schedule_id = $_POST['schedule_id'];
    $staff_id = $_POST['staff_id'];
    $available_date = $_POST['available_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    if ($start_time >= $end_time) {
        $_SESSION['error'] = "End time must be after start time.";
        header("Location: staff_schedule.php");
        exit();
    }

    // Check for schedule conflict
    $conflict_stmt = $conn->prepare("
        SELECT * FROM staff_scheduling 
        WHERE staff_id = ? AND available_date = ? 
        AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?)) 
        AND schedule_id != ?
    ");
    $conflict_stmt->bind_param("isssssi", $staff_id, $available_date, $end_time, $start_time, $start_time, $end_time, $schedule_id);
    $conflict_stmt->execute();
    $conflict_result = $conflict_stmt->get_result();

    if ($conflict_result->num_rows > 0) {
        $_SESSION['error'] = "Schedule conflict detected.";
        header("Location: staff_schedule.php");
        exit();
    }

    // Perform the update
    $update_stmt = $conn->prepare("UPDATE staff_scheduling SET staff_id = ?, available_date = ?, start_time = ?, end_time = ? WHERE schedule_id = ?");
    $update_stmt->bind_param("isssi", $staff_id, $available_date, $start_time, $end_time, $schedule_id);

    if ($update_stmt->execute()) {
        $_SESSION['success'] = "Schedule updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update schedule.";
    }

    $update_stmt->close();
    header("Location: staff_schedule.php");
    exit();
} else {
    header("Location: staff_schedule.php");
    exit();
}
