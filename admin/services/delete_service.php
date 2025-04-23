<?php
require_once '../includes/db_connection.php';

$id = $_POST['service_id'];
$sql = "DELETE FROM services WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

header("Location: salon_services.php");
exit;
