<?php
require_once 'includes/db_connection.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Atrium Salon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/landing_page.css">
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container d-flex align-items-center justify-content-between">
            <a class="navbar-brand" href="index.php">
                <img src="image/lo.jpg" alt="Logo"> 
                <span class="logo-text">The Atrium</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#servicesSection">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="customer/customer_login.php">Log In</a></li>
                    <li class="nav-item"><a class="nav-link" href="customer/customer_registration.php">Sign Up</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <h1>Welcome to Atrium</h1>
        <p>where beauty meets comfort, and every visit feels like home.</p>
        <a href="customer/appointment_booking.php" class="btn btn-lg btn-primary">Book Appointment</a>
        <div class="icon-links d-none d-md-flex">
            <a href="#" class="text-white"><i class="bi bi-facebook"></i></a>
            <a href="tel:+1234567890" class="text-white"><i class="bi bi-telephone-fill"></i></a>
            <a href="mailto:example@gmail.com" class="text-white"><i class="bi bi-envelope-fill"></i></a>
        </div>
    </div>

    <div class="container" style="position: relative;">
        <div class="services-section" id="servicesSection">
            <h2 class="text-center mb-5">Our Services</h2>

            <div class="scroll-wrapper d-flex overflow-auto gap-3" id="cardScrollContainer">
                <?php
                $query = "SELECT * FROM services";
                $result = mysqli_query($conn, $query);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<div class="service-card shadow-sm mb-4">';
                        echo '<img src="' . htmlspecialchars($row['image']) . '" class="card-img-top" alt="' . htmlspecialchars($row['service_name']) . '">';
                        echo '<div class="card-body">';
                        echo '<h5 class="card-title">' . htmlspecialchars($row['service_name']) . '</h5>';
                        echo '<p class="card-text">' . htmlspecialchars($row['description']) . '</p>';
                        echo '<p class="card-text">Price: ' . htmlspecialchars($row['price']) . '</p>';
                        echo '</div></div>';
                    }
                } else {
                    echo '<p class="text-center">No services available at the moment.</p>';
                }
                ?>
            </div>
        </div>

        <!-- Scroll Buttons -->
        <button class="scroll-button left" onclick="scrollCards(-300)">&#8249;</button>
        <button class="scroll-button right" onclick="scrollCards(300)">&#8250;</button>

        <script>
            function scrollCards(amount) {
                const container = document.getElementById('cardScrollContainer');
                container.scrollLeft += amount;
            }
        </script>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
        <p>&copy; <?php echo date('Y'); ?> Salon Appointment System. All Rights Reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
