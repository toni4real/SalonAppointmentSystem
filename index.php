<?php
require_once 'includes/db_connection.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salon Appointment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/landing_page.css">
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">The Atrium Salon</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="customer/customer_login.php">Log In</a></li>
                    <li class="nav-item"><a class="nav-link" href="customer/customer_registration.php">Sign Up</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div>
            <h1 class="display-4">Welcome to Our Salon</h1>
            <p class="lead">Experience luxury services by our professional staff.</p>
            <a href="customer/appointment_booking.php" class="btn btn-primary btn-lg">Book an Appointment</a>
        </div>
    </div>

    <!-- Services Section -->
    <div class="container services-section">
        <h2 class="text-center mb-5">Our Services</h2>
        <div class="row">
            <?php
            $query = "SELECT service_name, price FROM services LIMIT 3";
            $result = mysqli_query($conn, $query);
            while ($service = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-4">
                    <div class="card service-card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($service['service_name']); ?></h5>
                            <p class="card-text">Price: ₱<?php echo number_format($service['price'], 2); ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
        <p>&copy; <?php echo date('Y'); ?> Salon Appointment System. All Rights Reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
