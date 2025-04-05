<?php
require_once '../includes/db_connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    // Check if email already exists
    $check_query = "SELECT * FROM customers WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $error = 'Email is already registered.';
    } else {
        // Insert new customer
        $query = "INSERT INTO customers (name, email, password, phone) VALUES ('$name', '$email', '$password', '$phone')";

        if (mysqli_query($conn, $query)) {
            header('Location: customer_login.php');
            exit();
        } else {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration - Salon System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/customer_registration.css">
</head>

<body>
<div class="register-container">
        <div class="left-content">
            <div class="logo-container"></div>
            <h2>Customer Registration</h2>
            <button type="submit" class="btn btn-success" form="registrationForm">Register</button>
            <div class="links-container">
                <p>Already have an account? <a href="customer_login.php">Login here</a></p>
                <a href="../index.php">Back to Home</a>
            </div>
        </div>
        <form method="POST" action="" id="registrationForm" class="registration-form">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"> <?php echo $error; ?> </div>
            <?php endif; ?>
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
        </form>
    </div>
</body>

</html>
