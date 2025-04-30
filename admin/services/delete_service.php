<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

// Check if 'id' is set in POST data
if (!isset($_POST['id'])) {
    die("Service ID not provided.");
}

$service_id = $_POST['id'];

// Prepare and execute query to get the image path of the service
$sql = "SELECT image FROM services WHERE service_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $service_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$service = mysqli_fetch_assoc($result);

if ($service) {
    // Check if the service has an image and delete it from the folder
    $image_path = $service['image'];
    if ($image_path && file_exists('../../' . $image_path)) {
        unlink('../../' . $image_path);  // Delete the old image from the folder
    }

    // Prepare and execute the delete query to remove the service from the database
    $sql = "DELETE FROM services WHERE service_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $service_id);
    mysqli_stmt_execute($stmt);

    // Redirect back to the services list
    header("Location: ../services_list.php");
    exit;
} else {
    die("Service not found.");
}
