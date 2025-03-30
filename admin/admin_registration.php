<?php
require_once '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    $checkEmail = "SELECT * FROM admins WHERE email = '$email'";
    $result = mysqli_query($conn, $checkEmail);

    if (mysqli_num_rows($result) > 0) {
        $error = 'Email already exists!';
    } else {
        $query = "INSERT INTO admins (name, email, password, phone) VALUES ('$name', '$email', '$password', '$phone')";
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
    <title>Admin Registration - Salon System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color:rgb(252, 244, 244); /* Light pink background */
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
    }

    .register-container {
        background-color: #fff;
        padding: 50px;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        width: 850px; /* Adjust width to match image */
        display: flex;
        flex-direction: row;
        align-items: flex-start; /* Align items to the top */
        justify-content: space-between;
    }

    .left-content {
        flex: 0 0 40%; /* Adjust width for left side */
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding-right: 30px; /* Add some spacing */
    }

    .logo-container {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-color: #fff;
        border: 2px solid #cb6ce6;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 30px;
    }

    .logo-container::before {
        content: '';
        display: block;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #cb6ce6;
        mask: url('data:image/svg+xml,%3Csvg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"%3E%3Cpath d="M50 6a44 44 0 0 0-44 44c0 24.3 14.8 40.7 35.5 47.4 2.3.7 3.1 1.1 3.1 1.1v-2.6c0-1.5-.6-2.8-1.6-3.8-10.8-10.2-17.5-23.4-17.5-38.1a17.6 17.6 0 0 1 35.2 0c0 14.7-6.7 27.9-17.5 38.1-1 1-1.6 2.3-1.6 3.8v2.6s.8-.4 3.1-1.1c20.7-6.7 35.5-23.1 35.5-47.4a44 44 0 0 0-44-44z"/%3E%3C/svg%3E') center/contain no-repeat;
        -webkit-mask: url('data:image/svg+xml,%3Csvg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"%3E%3Cpath d="M50 6a44 44 0 0 0-44 44c0 24.3 14.8 40.7 35.5 47.4 2.3.7 3.1 1.1 3.1 1.1v-2.6c0-1.5-.6-2.8-1.6-3.8-10.8-10.2-17.5-23.4-17.5-38.1a17.6 17.6 0 0 1 35.2 0c0 14.7-6.7 27.9-17.5 38.1-1 1-1.6 2.3-1.6 3.8v2.6s.8-.4 3.1-1.1c20.7-6.7 35.5-23.1 35.5-47.4a44 44 0 0 0-44-44z"/%3E%3C/svg%3E') center/contain no-repeat;
    }

    h2 {
        color: #a78bfa;
        margin-bottom: 40px;
        font-size: 2.2em;
    }

    .btn-success {
        background-color: #a78bfa; /* Violet color */
        color: #fff;
        border: none;
        border-radius: 25px;
        padding: 12px 40px;
        font-size: 18px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        width: auto;
        margin-bottom: 20px;
    }

    .btn-success:hover {
        background-color: #9575cd; /* Darker violet on hover */
    }

    .links-container {
        text-align: center;
        margin-top: 15px;
    }

    .links-container p,
    .links-container a {
        font-size: 0.9em;
        color: #777;
        text-decoration: none;
    }

    .links-container a {
        color: #a78bfa;
        font-weight: bold;
    }

    .links-container a:hover {
        text-decoration: underline;
    }

    .registration-form {
        flex: 0 0 55%; /* Adjust width for right side */
        padding-left: 50px;
        display: flex;
        flex-direction: column;
    }

    .alert-danger {
        background-color: #fdecea;
        color: #d9534f;
        padding: 12px;
        border-radius: 25px;
        margin-bottom: 15px;
        text-align: center;
        font-size: 0.9em;
    }

    .mb-3 {
        margin-bottom: 20px !important;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-size: 0.9em;
        color: #555;
    }

    .form-control {
        width: calc(100% - 30px);
        padding: 12px 20px;
        border: 1px solid #ccc; /* Lighter border */
        border-radius: 5px; /* Less rounded inputs */
        font-size: 1em;
        box-sizing: border-box;
        outline: none;
        color: #333;
        background-color: #f9f9f9; /* Lighter background */
    }

    .form-control:focus {
        border-color: #a78bfa;
        box-shadow: 0 0 5px rgba(167, 139, 250, 0.3);
    }
    </style>
</head>
<body>
<div class="register-container">
        <div class="left-content">
            <div class="logo-container"></div>
            <h2>Admin Registration</h2>
            <button type="submit" class="btn btn-success" form="registrationForm">Register</button>
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
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
            </div>
        </form>
    </div>
</body>
</html>
