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

    .services-section {
        padding: 20px 40px;
        /* Add horizontal padding for buttons */
        overflow-x: auto;
        /* Enable horizontal scrolling */
        white-space: nowrap;
        /* Prevent cards from wrapping */
        -webkit-overflow-scrolling: touch;
        /* For smooth scrolling on iOS */
        scroll-behavior: smooth;
        /* Add smooth scrolling */
        position: relative;
        /* For scroll buttons */
    }

    .service-card {
        position: relative;
        /* Needed for absolute positioning of the overlay */
        border: none;
        border-radius: 10px;
        overflow: hidden;
        transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94), box-shadow 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        /* Smoother transition */
        display: inline-block;
        /* Arrange cards horizontally */
        width: 300px;
        /* Adjust card width as needed */
        margin-right: 20px;
        /* Spacing between cards */
        margin-bottom: 20px;
        /* Add some bottom margin for spacing if it wraps on smaller screens */
        opacity: 0;
        transform: translateX(-20px);
        animation: slideIn 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        /* Initial fade-in and slide */
    }

    @keyframes slideIn {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Add animation delay for a staggered effect */
    .service-card:nth-child(1) {
        animation-delay: 0.1s;
    }

    .service-card:nth-child(2) {
        animation-delay: 0.2s;
    }

    .service-card:nth-child(3) {
        animation-delay: 0.3s;
    }

    /* Add more for the number of cards you have */

    /* Remove margin from the last card */
    .service-card:last-child {
        margin-right: 0;
    }

    .service-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .service-card img {
        width: 100%;
        height: auto;
        display: block;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        object-fit: cover;
        aspect-ratio: 4 / 3;
        transition: filter 0.3s ease-in-out;
        /* Image hover effect */
    }

    .service-card:hover img {
        filter: brightness(1.1);
        /* Slightly brighten image on hover */
    }

    .service-card .card-body {
        padding: 15px;
        text-align: center;
        position: relative;
        /* To ensure text stays above the overlay */
        z-index: 1;
        /* To ensure text stays above the overlay */
    }

    .service-card .card-title {
        font-size: 1.25rem;
        margin-bottom: 5px;
        font-weight: bold;
        color: #333;
        /* Default text color */
        transition: color 0.3s ease-in-out;
        /* Transition for text color */
    }

    .service-card .card-text {
        font-size: 0.9rem;
        color: #6c757d;
        /* Default text color */
        margin-bottom: 10px;
        transition: color 0.3s ease-in-out;
        /* Transition for text color */
    }

    .shadow-sm {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .mb-5 {
        margin-bottom: 2rem !important;
        /* Reduced margin for heading */
        text-align: center;
    }

    /* Hover Gradient Overlay with smoother transition */
    .service-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0));
        /* Black gradient fading out to transparent */
        opacity: 0;
        /* Initially hidden */
        transition: opacity 0.3s ease-in-out;
        z-index: 0;
        /* Behind the text */
    }

    .service-card:hover::before {
        opacity: 1;
        /* Show the gradient on hover */
    }

    /* Optional: Change text color on hover for better contrast with smoother transition */
    .service-card:hover .card-title,
    .service-card:hover .card-text {
        color: #fff;
        transition: color 0.3s ease-in-out;
    }

    /* Hide scrollbar for Webkit browsers (Chrome, Safari) */
    .services-section::-webkit-scrollbar {
        display: none;
    }

    /* Hide scrollbar for Firefox */
    .services-section {
        scrollbar-width: none;
    }

    /* Scroll Buttons with smoother transition */
    .scroll-button {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0, 0, 0, 0.3);
        color: #fff;
        border: none;
        padding: 10px;
        margin: 0;
        border-radius: 5px;
        cursor: pointer;
        z-index: 10;
        opacity: 0.7;
        transition: opacity 0.3s ease-in-out, background-color 0.3s ease-in-out;
        /* Added background transition */
    }

    .scroll-button:hover {
        opacity: 1;
        background-color: rgba(0, 0, 0, 0.5);
        /* Darken on hover */
    }

    .scroll-button.left {
        left: 10px;
    }

    .scroll-button.right {
        right: 10px;
    }

    .scroll-button:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    /* Fading edges for better visual indication of scroll with smoother transition */
    .services-section::before,
    .services-section::after {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 50px;
        background: linear-gradient(to right, rgba(255, 255, 255, 0.8) 0%, rgba(255, 255, 255, 0) 100%);
        z-index: 5;
        pointer-events: none;
        transition: opacity 0.3s ease-in-out;
        /* Added opacity transition */
    }

    .services-section::after {
        right: 0;
        background: linear-gradient(to left, rgba(255, 255, 255, 0.8) 0%, rgba(255, 255, 255, 0) 100%);
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


    <div class="container" style="position: relative;">
        <div class="services-section" id="servicesSection">
            <h2 class="text-center mb-5">Our Services</h2>
            <div class="service-card shadow-sm mb-4">
                <img src="image/haircut.jpg" class="card-img-top" alt="Haircut Service">
                <div class="card-body">
                    <h5 class="card-title">Haircut</h5>
                    <p class="card-text">Lorem ipsum dolor sit amet...</p>
                    <p class="card-text">Price: ₱100.00</p>
                </div>
            </div>

            <div class="service-card shadow-sm mb-4">
                <img src="image/hair color.jpg" class="card-img-top" alt="Styling Service">
                <div class="card-body">
                    <h5 class="card-title">Hair Color</h5>
                    <p class="card-text">Lorem ipsum dolor sit amet...</p>
                    <p class="card-text">Price: ₱600.00 - ₱800.00</p>
                </div>
            </div>

            <div class="service-card shadow-sm mb-4">
                <img src="image/hairstyling.jpg" class="card-img-top" alt="Styling Service">
                <div class="card-body">
                    <h5 class="card-title">Hair Brazillian</h5>
                    <p class="card-text">Lorem ipsum dolor sit amet...</p>
                    <p class="card-text">Price: ₱1,500.00</p>
                </div>
            </div>

            <div class="service-card shadow-sm mb-4">
                <img src="image/hairspa.jpg" class="card-img-top" alt="Styling Service">
                <div class="card-body">
                    <h5 class="card-title">Hair Spa</h5>
                    <p class="card-text">Lorem ipsum dolor sit amet...</p>
                    <p class="card-text">Price: ₱600.00</p>
                </div>
            </div>

            <div class="service-card shadow-sm mb-4">
                <img src="image/rebonding.jpg" class="card-img-top" alt="Styling Service">
                <div class="card-body">
                    <h5 class="card-title">Rebond</h5>
                    <p class="card-text">Lorem ipsum dolor sit amet...</p>
                    <p class="card-text">Price: ₱2,500.00</p>
                </div>
            </div>

            <div class="service-card shadow-sm mb-4">
                <img src="image/balayage.jpg" class="card-img-top" alt="Styling Service">
                <div class="card-body">
                    <h5 class="card-title">Balayage</h5>
                    <p class="card-text">Lorem ipsum dolor sit amet...</p>
                    <p class="card-text">Price: ₱6,000.00</p>
                </div>
            </div>

            <div class="service-card shadow-sm mb-4">
                <img src="image/blower.jpg" class="card-img-top" alt="Styling Service">
                <div class="card-body">
                    <h5 class="card-title">Blower</h5>
                    <p class="card-text">Lorem ipsum dolor sit amet...</p>
                    <p class="card-text">Price: ₱150.00</p>
                </div>
            </div>

            <div class="service-card shadow-sm mb-4">
                <img src="image/iron.jpg" class="card-img-top" alt="Styling Service">
                <div class="card-body">
                    <h5 class="card-title">Iron</h5>
                    <p class="card-text">Lorem ipsum dolor sit amet...</p>
                    <p class="card-text">Price: ₱250.00</p>
                </div>
            </div>

            <div class="service-card shadow-sm mb-4">
                <img src="image/hotoil.jpg" class="card-img-top" alt="Styling Service">
                <div class="card-body">
                    <h5 class="card-title">Hot Oil Treatment</h5>
                    <p class="card-text">Lorem ipsum dolor sit amet...</p>
                    <p class="card-text">Price: ₱300.00</p>
                </div>
            </div>

            <div class="service-card shadow-sm mb-4">
                <img src="image/gel_polish.jpg" class="card-img-top" alt="Styling Service">
                <div class="card-body">
                    <h5 class="card-title">Gel Polish</h5>
                    <p class="card-text">Lorem ipsum dolor sit amet...</p>
                    <p class="card-text">Price: ₱500.00</p>
                </div>
            </div>

            <div class="service-card shadow-sm mb-4">
                <img src="image/Manicures.jpg" class="card-img-top" alt="Styling Service">
                <div class="card-body">
                    <h5 class="card-title">Manicure</h5>
                    <p class="card-text">Lorem ipsum dolor sit amet...</p>
                    <p class="card-text">Price: ₱120.00</p>
                </div>
            </div>

            <div class="service-card shadow-sm mb-4">
                <img src="image/pedicure.jpg" class="card-img-top" alt="Styling Service">
                <div class="card-body">
                    <h5 class="card-title">Pedicure</h5>
                    <p class="card-text">Lorem ipsum dolor sit amet...</p>
                    <p class="card-text">Price: ₱140.00</p>
                </div>
            </div>

            <div class="service-card shadow-sm mb-4">
                <img src="image/HandParaffin.jpg" class="card-img-top" alt="Styling Service">
                <div class="card-body">
                    <h5 class="card-title">Hand Paraffin</h5>
                    <p class="card-text">Lorem ipsum dolor sit amet...</p>
                    <p class="card-text">Price: ₱400.00</p>
                </div>
            </div>

            <div class="service-card shadow-sm mb-4">
                <img src="image/footparaffin.jpg" class="card-img-top" alt="Styling Service">
                <div class="card-body">
                    <h5 class="card-title">Foot Paraffin</h5>
                    <p class="card-text">Lorem ipsum dolor sit amet...</p>
                    <p class="card-text">Price: ₱600.00</p>
                </div>
            </div>

            <div class="service-card shadow-sm mb-4">
                <img src="image/footspa.jpg" class="card-img-top" alt="Styling Service">
                <div class="card-body">
                    <h5 class="card-title">Foot Spa</h5>
                    <p class="card-text">Lorem ipsum dolor sit amet...</p>
                    <p class="card-text">Price: ₱270.00</p>
                </div>
            </div>

            <div class="service-card shadow-sm mb-4">
                <img src="image/eyebrowshave.jpg" class="card-img-top" alt="Styling Service">
                <div class="card-body">
                    <h5 class="card-title">Eyebrow Shave</h5>
                    <p class="card-text">Lorem ipsum dolor sit amet...</p>
                    <p class="card-text">Price: ₱60.00</p>
                </div>
            </div>

            <div class="service-card shadow-sm mb-4">
                <img src="image/eyebrowthreading.jpg" class="card-img-top" alt="Styling Service">
                <div class="card-body">
                    <h5 class="card-title">Eyebrow threading</h5>
                    <p class="card-text">Lorem ipsum dolor sit amet...</p>
                    <p class="card-text">Price: ₱150.00</p>
                </div>
            </div>

            <div class="service-card shadow-sm mb-4">
                <img src="image/facethreading.jpg" class="card-img-top" alt="Styling Service">
                <div class="card-body">
                    <h5 class="card-title">Face Threading</h5>
                    <p class="card-text">Lorem ipsum dolor sit amet...</p>
                    <p class="card-text">Price: ₱350.00</p>
                </div>
            </div>
        </div>
        <button class="scroll-button left" onclick="document.getElementById('servicesSection').scrollLeft -= 300;">&#8249;</button>
        <button class="scroll-button right" onclick="document.getElementById('servicesSection').scrollLeft += 300;">&#8250;</button>
    </div>

    <h2 class="text-center mb-5">Our Services</h2>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
        <p>&copy; <?php echo date('Y'); ?> Salon Appointment System. All Rights Reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>