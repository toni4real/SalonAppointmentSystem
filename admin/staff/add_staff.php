<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

if (isset($_POST['add_staff'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $admin_id = $_SESSION['admin_id'];

    $query = "INSERT INTO staff (first_name, last_name, role, status, email, phone, admin_id) VALUES ('$first_name', '$last_name', '$role', '$status', '$email', '$phone', '$admin_id')";
    if (mysqli_query($conn, $query)) {
        header('Location: ../staff_management.php');
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
