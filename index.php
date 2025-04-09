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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/landing_page.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container d-flex align-items-center justify-content-between">
            <!-- Logo -->
            <a class="navbar-brand" href="index.php">
                <img src="image/lo.jpg" alt="Logo"> 
                 <span class="logo-text">The Atrium</span> 
            </a>

            <!-- Navbar Toggler for Mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Login/Signup -->
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#servicesSection">Services</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Help
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">User Manual</a></li>
                            <li><a class="dropdown-item" href="#">Another action</a></li>
                            <li><a class="dropdown-item" href="#">Something else here</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="customer/customer_login.php">Log In</a></li>
                    <li class="nav-item"><a class="nav-link" href="customer/customer_registration.php">Sign Up</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <h1>Welcome to Atrium!</h1>
        <p>where beauty meets comfort, and every visit feels like home.</p>
        <a href="customer/appointment_booking.php" class="btn btn-lg btn-primary">Book Appointment</a>
        <!-- Icons -->
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
                <div class="service-card shadow-sm mb-4">
                    <img src="image/haircut.jpg" class="card-img-top" alt="Classic haircut service">
                    <div class="card-body">
                        <h5 class="card-title">Haircut</h5>
                        <p class="card-text">Get a fresh new look with our stylish haircut tailored to your face shape.</p>
                        <p class="card-text">Price: ₱100.00</p>
                    </div>
                </div>
                <div class="service-card shadow-sm mb-4">
                    <img src="image/hair color.jpg" class="card-img-top" alt="Hair coloring service">
                    <div class="card-body">
                        <h5 class="card-title">Hair Color</h5>
                        <p class="card-text">Enhance your style with vibrant hair colors that last longer.</p>
                        <p class="card-text">Price: ₱600.00 - ₱800.00</p>
                    </div>
                </div>
                <div class="service-card shadow-sm mb-4">
                    <img src="image/hairstyling.jpg" class="card-img-top" alt="Brazilian hair treatment">
                    <div class="card-body">
                        <h5 class="card-title">Hair Brazillian</h5>
                        <p class="card-text">Smooth, shiny, and frizz-free hair with our Brazilian treatment.</p>
                        <p class="card-text">Price: ₱1,500.00</p>
                    </div>
                </div>
                <div class="service-card shadow-sm mb-4">
                    <img src="image/hairspa.jpg" class="card-img-top" alt="Hair spa relaxation treatment">
                    <div class="card-body">
                        <h5 class="card-title">Hair Spa</h5>
                        <p class="card-text">Revive dry, damaged hair with our relaxing hair spa therapy.</p>
                        <p class="card-text">Price: ₱600.00</p>
                    </div>
                </div>
                <div class="service-card shadow-sm mb-4">
                <img src="image/rebonding.jpg" class="card-img-top" alt="Hair rebonding service">
                    <div class="card-body">
                        <h5 class="card-title">Rebond</h5>
                        <p class="card-text">Achieve sleek, straight hair with our premium rebonding service.</p>
                        <p class="card-text">Price: ₱2,500.00</p>
                    </div>
                </div>
                <div class="service-card shadow-sm mb-4">
                    <img src="image/balayage.jpg" class="card-img-top" alt="Balayage hair coloring">
                    <div class="card-body">
                        <h5 class="card-title">Balayage</h5>
                        <p class="card-text">Modern balayage for natural-looking, sun-kissed highlights.</p>
                        <p class="card-text">Price: ₱6,000.00</p>
                    </div>
                </div>
                <div class="service-card shadow-sm mb-4">
                    <img src="image/blower.jpg" class="card-img-top" alt="Hair blower styling">
                    <div class="card-body">
                        <h5 class="card-title">Blower</h5>
                        <p class="card-text">Add volume and bounce to your hair with our expert blow-dry.</p>
                        <p class="card-text">Price: ₱150.00</p>
                    </div>
                </div>
                <div class="service-card shadow-sm mb-4">
                    <img src="image/iron.jpg" class="card-img-top" alt="Hair ironing service">
                    <div class="card-body">
                        <h5 class="card-title">Iron</h5>
                        <p class="card-text">Perfectly straight hair using high-quality flat iron techniques.</p>
                        <p class="card-text">Price: ₱250.00</p>
                    </div>
                </div>
                <div class="service-card shadow-sm mb-4">
                    <img src="image/hotoil.jpg" class="card-img-top" alt="Hot oil hair treatment">
                    <div class="card-body">
                        <h5 class="card-title">Hot Oil Treatment</h5>
                        <p class="card-text">Nourish your scalp and strands with our hot oil treatment.</p>
                        <p class="card-text">Price: ₱300.00</p>
                    </div>
                </div>
                <div class="service-card shadow-sm mb-4">
                    <img src="image/gel_polish.jpg" class="card-img-top" alt="Gel polish for nails">
                    <div class="card-body">
                        <h5 class="card-title">Gel Polish</h5>
                        <p class="card-text">Shiny, chip-free nails with long-lasting gel polish application.</p>
                        <p class="card-text">Price: ₱500.00</p>
                    </div>
                </div>
                <div class="service-card shadow-sm mb-4">
                    <img src="image/Manicures.jpg" class="card-img-top" alt="Manicure service">
                    <div class="card-body">
                        <h5 class="card-title">Manicure</h5>
                        <p class="card-text">Clean, trimmed nails and soft hands with our manicure service.</p>
                        <p class="card-text">Price: ₱120.00</p>
                    </div>
                </div>
                <div class="service-card shadow-sm mb-4">
                    <img src="image/pedicure.jpg" class="card-img-top" alt="Pedicure service">
                    <div class="card-body">
                        <h5 class="card-title">Pedicure</h5>
                        <p class="card-text">Pamper your feet with a classic pedicure and nail care.</p>
                        <p class="card-text">Price: ₱140.00</p>
                    </div>
                </div>
                <div class="service-card shadow-sm mb-4">
                    <img src="image/HandParaffin.jpg" class="card-img-top" alt="Hand paraffin wax treatment">
                    <div class="card-body">
                        <h5 class="card-title">Hand Paraffin</h5>
                        <p class="card-text">Soften and moisturize hands with our warm paraffin wax treatment.</p>
                        <p class="card-text">Price: ₱400.00</p>
                    </div>
                </div>
                <div class="service-card shadow-sm mb-4">
                    <img src="image/footparaffin.jpg" class="card-img-top" alt="Foot paraffin wax treatment">
                    <div class="card-body">
                        <h5 class="card-title">Foot Paraffin</h5>
                        <p class="card-text">Treat cracked heels and tired feet with a soothing paraffin wrap.</p>
                        <p class="card-text">Price: ₱600.00</p>
                    </div>
                </div>
                <div class="service-card shadow-sm mb-4">
                    <img src="image/footspa.jpg" class="card-img-top" alt="Foot spa treatment">
                    <div class="card-body">
                        <h5 class="card-title">Foot Spa</h5>
                        <p class="card-text">Remove dead skin and calluses with our rejuvenating foot spa.</p>
                        <p class="card-text">Price: ₱270.00</p>
                    </div>
                </div>
                <div class="service-card shadow-sm mb-4">
                    <img src="image/eyebrowshave.jpg" class="card-img-top" alt="Eyebrow shaving service">
                    <div class="card-body">
                        <h5 class="card-title">Eyebrow Shave</h5>
                        <p class="card-text">Shape your brows to perfection with our gentle shaving method.</p>
                        <p class="card-text">Price: ₱60.00</p>
                    </div>
                </div>
                <div class="service-card shadow-sm mb-4">
                    <img src="image/eyebrowthreading.jpg" class="card-img-top" alt="Eyebrow threading service">
                    <div class="card-body">
                        <h5 class="card-title">Eyebrow threading</h5>
                        <p class="card-text">Get precise, natural-looking brows with expert threading.</p>
                        <p class="card-text">Price: ₱150.00</p>
                    </div>
                </div>
                <div class="service-card shadow-sm mb-4">
                    <img src="image/facethreading.jpg" class="card-img-top" alt="Face threading service">
                    <div class="card-body">
                        <h5 class="card-title">Face Threading</h5>
                        <p class="card-text">Smooth and hair-free face with our full face threading session.</p>
                        <p class="card-text">Price: ₱350.00</p>
                    </div>
                </div>
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