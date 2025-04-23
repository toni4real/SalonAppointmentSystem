<?php
require_once '../includes/db_connection.php';

$id = $_POST['service_id'];
$name = $_POST['service_name'];
$description = $_POST['description'];
$price = $_POST['price'];

$image_path = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $filename = basename($_FILES['image']['service_name']);
    $targetPath = '../uploads/' . $filename;
    move_uploaded_file($_FILES['image']['tmp_name'], $targetPath);
    $image_path = 'image/' . $filename;

    $sql = "UPDATE services SET service_name=?, description=?, price=?, image=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssi", $name, $description, $price, $image, $id);
} else {
    $sql = "UPDATE services SET service_name=?, description=?, price=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $name, $description, $price, $id);
}

mysqli_stmt_execute($stmt);
header("Location: salon_services.php");
exit;
