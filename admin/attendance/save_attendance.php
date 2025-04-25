<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance'])) {
    $today = date('F j, Y');

    foreach ($_POST['attendance'] as $staff_id => $status) {
        $query = "INSERT INTO staff_attendance (staff_id, attendance_date, status) 
                  VALUES (?, ?, ?)
                  ON DUPLICATE KEY UPDATE status = ?";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'isss', $staff_id, $today, $status, $status);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            die('SQL Error: ' . mysqli_error($conn));
        }
    }

    header('Location: ../staff_attendance.php?saved=1');
    exit;
}
?>
