<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';

$name = $_POST['service_name'] ?? null;
$description = $_POST['description'] ?? null;
$price = $_POST['price'] ?? null;

$image_path = '';

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $filename = basename($_FILES['image']['name']);
    $uploadDir = '../../image/';
    $targetPath = $uploadDir . $filename;

    // Ensure the directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $image_path = 'image/' . $filename; // Relative to the main project directory
    } else {
        die("Error uploading the file.");
    }
}

// Validate required fields
if (!$name || !$description || !$price) {
    die("All fields are required.");
}

$sql = "INSERT INTO services (service_name, description, price, image) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssss", $name, $description, $price, $image_path);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../services_list.php");
    exit;
} else {
    die("Database error: " . mysqli_error($conn));
}
