<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

if (isset($_POST['update_staff'])) {
    $staff_id = $_POST['staff_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $query = "UPDATE staff SET first_name='$first_name', last_name='$last_name', role='$role', status='$status', email='$email', phone='$phone' WHERE staff_id='$staff_id'";
    if (mysqli_query($conn, $query)) {
        header('Location: ../staff_management.php');
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
