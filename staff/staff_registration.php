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
    <link rel="stylesheet" href="../css/staff_registration.css">
    <style>
     body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color:#C8E4B2; /* Light green background */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
        }

        .register-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            width: 800px; /* Adjust width to match image */
            display: flex;
            flex-direction: row;
            align-items: center; /* Center items vertically */
            justify-content: space-between;
        }

        .left-content {
            flex: 0 0 30%; /* Adjusted width for left side */
            display: flex;
            flex-direction: column;
            align-items: center; /* Center items horizontally */
            text-align: center;
        }

        .logo-container {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #fff;
            border: 2px solid #77DD77; /* Slightly darker green border */
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px; /* Reduced margin */
        }

        .logo-container::before {
            content: '';
            display: block;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #77DD77; /* Slightly darker green */
            mask: url('data:image/svg+xml,%3Csvg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"%3E%3Cpath d="M50 6a44 44 0 0 0-44 44c0 24.3 14.8 40.7 35.5 47.4 2.3.7 3.1 1.1 3.1 1.1v-2.6c0-1.5-.6-2.8-1.6-3.8-10.8-10.2-17.5-23.4-17.5-38.1a17.6 17.6 0 0 1 35.2 0c0 14.7-6.7 27.9-17.5 38.1-1 1-1.6 2.3-1.6 3.8v2.6s.8-.4 3.1-1.1c20.7-6.7 35.5-23.1 35.5-47.4a44 44 0 0 0-44-44z"/%3E%3C/svg%3E') center/contain no-repeat;
            -webkit-mask: url('data:image/svg+xml,%3Csvg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"%3E%3Cpath d="M50 6a44 44 0 0 0-44 44c0 24.3 14.8 40.7 35.5 47.4 2.3.7 3.1 1.1 3.1 1.1v-2.6c0-1.5-.6-2.8-1.6-3.8-10.8-10.2-17.5-23.4-17.5-38.1a17.6 17.6 0 0 1 35.2 0c0 14.7-6.7 27.9-17.5 38.1-1 1-1.6 2.3-1.6 3.8v2.6s.8-.4 3.1-1.1c20.7-6.7 35.5-23.1 35.5-47.4a44 44 0 0 0-44-44z"/%3E%3C/svg%3E') center/contain no-repeat;
        }

        h2 {
            color: #6B8E23; /* Olive green */
            margin-bottom: 20px; /* Reduced margin */
            font-size: 2em; /* Slightly smaller font */
        }

        .btn-success {
            background-color: #77DD77; /* Slightly darker green */
            color: #fff;
            border: none;
            border-radius: 20px; /* Less rounded button */
            padding: 10px 30px; /* Smaller padding */
            font-size: 16px; /* Smaller font */
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: auto;
            margin-bottom: 15px; /* Reduced margin */
        }

        .btn-success:hover {
            background-color: #6AB047; /* Darker green on hover */
        }

        .links-container {
            text-align: center;
            margin-top: 10px; /* Reduced margin */
        }

        .links-container p,
        .links-container a {
            font-size: 0.8em; /* Smaller font */
            color: #556B2F; /* Dark olive green */
            text-decoration: none;
        }

        .links-container a {
            color: #77DD77; /* Slightly darker green */
            font-weight: bold;
        }

        .links-container a:hover {
            text-decoration: underline;
        }

        .registration-form {
            flex: 0 0 65%; /* Adjusted width for right side */
            padding-left: 50px;
            display: flex;
            flex-direction: column;
        }

        .alert-danger {
            background-color: #F0FFF0; /* Lightest green */
            color: #8B0000; /* Dark red for error */
            padding: 10px; /* Smaller padding */
            border-radius: 20px; /* Less rounded error box */
            margin-bottom: 10px; /* Reduced margin */
            text-align: center;
            font-size: 0.8em; /* Smaller font */
        }

        .mb-3 {
            margin-bottom: 15px !important; /* Reduced margin */
        }

        .form-label {
            display: block;
            margin-bottom: 5px; /* Reduced margin */
            font-size: 0.8em; /* Smaller font */
            color: #555;
        }

        .form-control {
            width: calc(100% - 80px); /* Adjusted width for padding */
            padding: 10px 15px; /* Smaller padding */
            border: 1px solid #8FBC8F; /* Light slate gray-ish green */
            border-radius: 5px; /* Less rounded inputs */
            font-size: 0.9em; /* Smaller font */
            box-sizing: border-box;
            outline: none;
            color: #333;
            background-color: #ECF2E9; /* Very light green */
        }

        .form-control:focus {
            border-color: #77DD77; /* Slightly darker green */
            box-shadow: 0 0 5px rgba(119, 221, 119, 0.5); /* Green shadow */
        }
    </style>
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
            <label for="name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select name="role" class="form-control" id="role" required>
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
