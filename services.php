<?php
    require_once('connection.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaRs | Our Services</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <script src="https://kit.fontawesome.com/dbed6b6114.js" crossorigin="anonymous"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f5f5f5;
        }

        /* Navigation Styles */
        .navbar {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1a1a;
            text-decoration: none;
            background: linear-gradient(45deg, #4158D0, #C850C0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            text-decoration: none;
            color: #666;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #4158D0;
        }

        .hamburger {
            display: none;
            cursor: pointer;
        }

        /* Hero Section */
        .hero {
            margin-top: 80px;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            padding: 100px 20px;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.9;
        }

        /* Services Section */
        .services {
            max-width: 1200px;
            margin: 4rem auto;
            padding: 0 2rem;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: #1a1a1a;
            margin-bottom: 1rem;
        }

        .section-title p {
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .service-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-10px);
        }

        .service-image {
            height: 200px;
            overflow: hidden;
        }

        .service-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .service-card:hover .service-image img {
            transform: scale(1.1);
        }

        .service-content {
            padding: 2rem;
        }

        .service-content h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #1a1a1a;
        }

        .service-content p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .service-features {
            list-style: none;
            margin-bottom: 1.5rem;
        }

        .service-features li {
            color: #666;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .service-features li i {
            color: #4158D0;
            margin-right: 0.5rem;
        }

        .cta-button {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: linear-gradient(45deg, #4158D0, #C850C0);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: transform 0.3s ease;
        }

        .cta-button:hover {
            transform: translateY(-2px);
        }

        /* Footer Styles */
        footer {
            background: #1a1a1a;
            color: white;
            padding: 4rem 2rem 2rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }

        .footer-section h3 {
            color: white;
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
        }

        .footer-section p {
            color: #999;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.8rem;
        }

        .footer-links a {
            color: #999;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #4158D0;
        }

        .footer-bottom {
            text-align: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #333;
            color: #999;
        }

        @media (max-width: 900px) {
            .nav-links {
                display: none;
            }

            .hamburger {
                display: block;
            }

            .services-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer-content {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .services-grid {
                grid-template-columns: 1fr;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .footer-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">CaRs</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="cardetails.php">Cars</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a href="aboutus.html">About</a></li>
                <li><a href="contactus.html">Contact</a></li>
                <li><a href="register.php">Login/Register</a></li>
            </ul>
            <div class="hamburger">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" data-aos="fade-up">
        <h1>Our Premium Services</h1>
        <p>Experience luxury and comfort with our comprehensive car rental services</p>
    </section>

    <!-- Services Section -->
    <section class="services">
        <div class="section-title" data-aos="fade-up">
            <h2>What We Offer</h2>
            <p>Choose from our wide range of premium services designed to meet your every need</p>
        </div>

        <div class="services-grid">
            <!-- Luxury Car Rentals -->
            <div class="service-card" data-aos="fade-up">
                <div class="service-image">
                    <img src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&q=80" alt="Luxury Cars">
                </div>
                <div class="service-content">
                    <h3>Luxury Car Rentals</h3>
                    <p>Experience the epitome of automotive excellence with our luxury car collection.</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Premium vehicles from top brands</li>
                        <li><i class="fas fa-check"></i> Professional chauffeur service</li>
                        <li><i class="fas fa-check"></i> Flexible rental periods</li>
                    </ul>
                    <a href="cardetails.php" class="cta-button">View Fleet</a>
                </div>
            </div>

            <!-- Business Travel -->
            <div class="service-card" data-aos="fade-up" data-aos-delay="100">
                <div class="service-image">
                    <img src="https://images.unsplash.com/photo-1511527661048-7fe73d85e9a4?auto=format&fit=crop&q=80" alt="Business Travel">
                </div>
                <div class="service-content">
                    <h3>Business Travel</h3>
                    <p>Professional transportation solutions for your business needs.</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Corporate accounts</li>
                        <li><i class="fas fa-check"></i> Airport transfers</li>
                        <li><i class="fas fa-check"></i> 24/7 support</li>
                    </ul>
                    <a href="#" class="cta-button">Learn More</a>
                </div>
            </div>

            <!-- Special Events -->
            <div class="service-card" data-aos="fade-up" data-aos-delay="200">
                <div class="service-image">
                    <img src="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&q=80" alt="Special Events">
                </div>
                <div class="service-content">
                    <h3>Special Events</h3>
                    <p>Make your special day extraordinary with our premium vehicles.</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Wedding packages</li>
                        <li><i class="fas fa-check"></i> Photo shoot rentals</li>
                        <li><i class="fas fa-check"></i> Custom decorations</li>
                    </ul>
                    <a href="#" class="cta-button">Book Now</a>
                </div>
            </div>

            <!-- Long Term Rental -->
            <div class="service-card" data-aos="fade-up" data-aos-delay="300">
                <div class="service-image">
                    <img src="https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&q=80" alt="Long Term Rental">
                </div>
                <div class="service-content">
                    <h3>Long Term Rental</h3>
                    <p>Flexible long-term solutions for extended stays and projects.</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Competitive monthly rates</li>
                        <li><i class="fas fa-check"></i> Maintenance included</li>
                        <li><i class="fas fa-check"></i> Flexible terms</li>
                    </ul>
                    <a href="#" class="cta-button">Get Quote</a>
                </div>
            </div>

            <!-- Sport Cars -->
            <div class="service-card" data-aos="fade-up" data-aos-delay="400">
                <div class="service-image">
                    <img src="https://images.unsplash.com/photo-1494905998402-395d579af36f?auto=format&fit=crop&q=80" alt="Sport Cars">
                </div>
                <div class="service-content">
                    <h3>Sport Cars</h3>
                    <p>Experience the thrill of high-performance sports cars.</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Latest models</li>
                        <li><i class="fas fa-check"></i> Track day packages</li>
                        <li><i class="fas fa-check"></i> Expert guidance</li>
                    </ul>
                    <a href="#" class="cta-button">Explore Cars</a>
                </div>
            </div>

            <!-- Premium SUVs -->
            <div class="service-card" data-aos="fade-up" data-aos-delay="500">
                <div class="service-image">
                    <img src="https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&q=80" alt="Premium SUVs">
                </div>
                <div class="service-content">
                    <h3>Premium SUVs</h3>
                    <p>Luxury SUVs for comfortable family trips and adventures.</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Spacious interiors</li>
                        <li><i class="fas fa-check"></i> Advanced safety features</li>
                        <li><i class="fas fa-check"></i> All-terrain capability</li>
                    </ul>
                    <a href="#" class="cta-button">View SUVs</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>About CaRs</h3>
                <p>Your premier destination for luxury car rentals. We provide exceptional service and unforgettable driving experiences.</p>
            </div>

            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="cardetails.php">Cars</a></li>
                    <li><a href="aboutus.html">About Us</a></li>
                    <li><a href="contactus.html">Contact</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Our Services</h3>
                <ul class="footer-links">
                    <li><a href="#">Luxury Cars</a></li>
                    <li><a href="#">Sports Cars</a></li>
                    <li><a href="#">Premium SUVs</a></li>
                    <li><a href="#">Special Events</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Contact Info</h3>
                <ul class="footer-links">
                    <li><i class="fas fa-phone"></i> +91 81246 76002</li>
                    <li><i class="fas fa-phone"></i> +91 93420 90144</li>
                    <li><i class="fas fa-envelope"></i> onlinecarrental@gmail.com</li>
                    <li><i class="fas fa-map-marker-alt"></i> 123 Car Street, Katpadi, India</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2025 CaRs. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });

        // Mobile menu toggle
        document.querySelector('.hamburger').addEventListener('click', function() {
            document.querySelector('.nav-links').style.display = 
                document.querySelector('.nav-links').style.display === 'flex' ? 'none' : 'flex';
        });
    </script>
</body>
</html>
