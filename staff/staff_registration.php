<?php
require_once '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Format first and last names: lowercase first, then uppercase first letter
    $first_name = ucfirst(strtolower(trim($_POST['first_name'])));
    $last_name = ucfirst(strtolower(trim($_POST['last_name'])));
    $role = $_POST['role'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $admin_id = 1; // Replace with dynamic logic if needed

    $stmt = $conn->prepare("INSERT INTO staff (first_name, last_name, role, status, email, password, phone, admin_id) VALUES (?, ?, ?, 'active', ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $first_name, $last_name, $role, $email, $password, $phone, $admin_id);

    if ($stmt->execute()) {
        header("Location: staff_login.php");
        exit();
    } else {
        $error = "Registration failed. Please try again.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/staff_registration.css">
</head>
<body>
<div class="register-container">
    <div class="left-content">
        <div class="logo-container"></div>
        <h2>Staff Registration</h2>
        <button type="submit" class="btn btn-success" form="registrationForm">Register</button>
        <div class="links-container">
            <p>Already have an account? <a href="staff_login.php">Login here</a></p>
            <a href="../index.php">Back to Home</a>
        </div>
    </div>

    <form method="POST" action="" id="registrationForm" class="registration-form">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"> <?php echo $error; ?> </div>
        <?php endif; ?>

        <div class="mb-3">
            <label for="first_name" class="form-label">First Name</label>
            <input type="text" class="form-control" id="first_name" name="first_name" required>
        </div>

        <div class="mb-3">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="last_name" name="last_name" required>
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select name="role" class="form-control" id="role" required>
                <option value="" disabled selected hidden>Role</option>
                <option value="Hairstylist">Hairstylist</option>
                <option value="Barber">Barber</option>
                <option value="Beautician/Nail Technician">Beautician/Nail Technician</option>
                <option value="Junior Stylist">Junior Stylist</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="text" class="form-control" id="phone" name="phone" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
    </form>
</div>
</body>
</html>
