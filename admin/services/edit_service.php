<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

$id = $_POST['service_id'];
$name = $_POST['service_name'];
$description = $_POST['description'];
$price = $_POST['price'];

$image_path = null;

// Get the current image path from the database
$sql = "SELECT image FROM services WHERE service_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$currentImage = mysqli_fetch_assoc($result)['image'];

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $filename = basename($_FILES['image']['name']);
    $uploadDir = '../../image/';
    $targetPath = $uploadDir . $filename;

    // Ensure directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Check if there's an existing image and delete it
    if ($currentImage && file_exists('../../' . $currentImage)) {
        unlink('../../' . $currentImage);  // Delete the old image
    }

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $image_path = 'image/' . $filename;

        $sql = "UPDATE services SET service_name = ?, description = ?, price = ?, image = ? WHERE service_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $name, $description, $price, $image_path, $id);
    } else {
        die("Failed to upload the new image.");
    }
} else {
    $sql = "UPDATE services SET service_name = ?, description = ?, price = ? WHERE service_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $name, $description, $price, $id);
}

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../services_list.php");
    exit;
} else {
    die("Failed to update service: " . mysqli_error($conn));
}
