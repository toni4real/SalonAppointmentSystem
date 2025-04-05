<?php
require_once '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $role = $_POST['role'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $admin_id = 1; // static or dynamic

    $stmt = $conn->prepare("INSERT INTO staff (name, role, status, phone, email, admin_id, password) VALUES (?, ?, 'active', ?, ?, ?, ?)");
    $stmt->bind_param("ssssis", $name, $role, $phone, $email, $admin_id, $password);
    $stmt->execute();

    header("Location: staff_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #00b09b, #96c93d);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-box {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
    </style>
</head>
<body>
<div class="register-box">
    <h3 class="text-center mb-4">Staff Registration</h3>
    <form method="POST">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Role</label>
            <select name="role" class="form-control" required>
                <option value="Hairstylist">Hairstylist</option>
                <option value="Barber">Barber</option>
                <option value="Beautician/Nail Technician">Beautician/Nail Technician</option>
                <option value="Junior Stylist">Junior Stylist</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" required maxlength="11">
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-success w-100" type="submit">Register</button>
    </form>
    <p class="text-center mt-3">Already have an account? <a href="staff_login.php">Login</a></p>
</div>
</body>
</html>
