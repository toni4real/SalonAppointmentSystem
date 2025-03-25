<?php
// Database Connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'salon_appointment_system';

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
