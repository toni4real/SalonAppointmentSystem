<?php
require_once '../includes/db_connection.php';

$name = $_POST['service_name'];
$description = $_POST['description'];
$price = $_POST['price'];

$image_path = '';

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $filename = basename($_FILES['image']['service_name']);
    $targetPath = '../image/' . $filename;
    move_uploaded_file($_FILES['image']['tmp_name'], $targetPath);
    $image_path = 'image/' . $filename;
}

$sql = "INSERT INTO services (service_name, description, price, image) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssss", $name, $description, $price, $image_path);
mysqli_stmt_execute($stmt);

header("Location: salon_services.php");
exit;
