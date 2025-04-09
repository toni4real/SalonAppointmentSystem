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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/landing_page.css">
</head>
<style>
    
    body,
    html {
      height: 100%;
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: white;
    }

    .navbar {
      background-color: #d276e2;
    }

    .navbar-brand img {
      height: 40px;
    }

    .nav-link {
      color: white !important;
      margin-left: 20px;
    }

    .icon-links i {
      font-size: 20px;
      color: white;
      margin-right: 20px;
    }

    .hero-section {
      background-image: url('image/salon.png');
      background-size: cover;
      background-position: center;
      height: 100vh;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      flex-direction: column;
      padding: 20px;
    }

    .hero-section h1 {
      font-size: 10rem;
      font-family: 'Brush Script MT', cursive;
    }

    .hero-section p {
      font-size: 1.5rem;
      margin: 20px 0;
    }

    .hero-section .btn {
      background-color: #d276e2;
      border: none;
      padding: 10px 30px;
    }

    </style>
<body>
 <!-- Navigation Bar -->
 <nav class="navbar navbar-expand-lg">
    <div class="container d-flex align-items-center justify-content-between">
      <!-- Logo -->
      <a class="navbar-brand" href="#">
        <img src="image/lo.jpg" alt="Logo">
      </a>

      <!-- Login/Signup -->
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="customer/customer_login.php">LOG IN</a></li>
          <li class="nav-item"><a class="nav-link" href="customer/customer_registration.php">SIGN UP</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <div class="hero-section">
    <h1>Welcome to Atrium!</h1>
    <p>where beauty meets comfort, and every visit feels like home.</p>
    <a href="customer/appointment_booking.php" class="btn btn-lg text-white">Book Appointment</a>
          <!-- Icons -->
          <div class="icon-links d-none d-md-flex">
        <a href="#" class="text-white"><i class="bi bi-facebook"></i></a>
        <a href="tel:+1234567890" class="text-white"><i class="bi bi-telephone-fill"></i></a>
        <a href="mailto:example@gmail.com" class="text-white"><i class="bi bi-envelope-fill"></i></a>
      </div>
  </div>

  <!-- Bootstrap JS (optional) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
