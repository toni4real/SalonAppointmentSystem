<?php
require_once '../includes/db_connection.php';

if (isset($_POST['delete_staff'])) {
    $staff_id = $_POST['staff_id'];

    $query = "DELETE FROM staff WHERE staff_id='$staff_id'";
    if (mysqli_query($conn, $query)) {
        header('Location: staff_management.php');
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
