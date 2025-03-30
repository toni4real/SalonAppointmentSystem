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
            background: linear-gradient(to right, #4e54c8, #8f94fb);
        }
        .register-container {
            max-width: 500px;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        a {
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2 class="text-center mb-4">Admin Registration</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"> <?php echo $error; ?> </div>
        <?php endif; ?>
        <form method="POST" action="">
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
            <button type="submit" class="btn btn-success w-100">Register</button>
            <p class="mt-3 text-center">Already have an account? <a href="admin_login.php">Login here</a></p>
            <a href="../index.php">Back to Home</a>
        </form>
    </div>
</body>
</html>
