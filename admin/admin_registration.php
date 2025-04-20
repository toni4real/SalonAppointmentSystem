<?php
require_once '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);    
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    $checkEmail = "SELECT * FROM admins WHERE email = '$email'";
    $result = mysqli_query($conn, $checkEmail);

    if (mysqli_num_rows($result) > 0) {
        $error = 'Email already exists!';
    } else {
        $query = "INSERT INTO admins (first_name, last_name, email, password, phone) 
                VALUES ('$first_name', '$last_name', '$email', '$password', '$phone')";
        if (mysqli_query($conn, $query)) {
            header('Location: admin_login.php');
            exit();
        } else {
            $error = 'Registration failed. Try again!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/admin_registration.css">
</head>
<body>
<div class="register-container">
        <div class="left-content">
            <i class="bi bi-person-circle"></i>
            <h2>Admin Registration</h2>
            <button type="submit" class="btn" form="registrationForm">Register</button>
            <div class="links-container">
                <p>Already have an account? <a href="admin_login.php">Login here</a></p>
                <a href="../index.php">Back to Home</a>
            </div>
        </div>
        <form method="POST" action="" id="registrationForm" class="registration-form">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"> <?php echo $error; ?> </div>
            <?php endif; ?>
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter first name" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter last name" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter phone number" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
            </div>
        </form>
    </div>
</body>
</html>
