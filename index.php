<?php
require_once('connection.php');
session_start();

if(isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = mysqli_real_escape_string($conn, $_POST['pass']);
    $pass = md5($pass);
    
    if(empty($email) || empty($pass)) {
        $error = "Please fill in all fields";
    } else {
        $query = "SELECT * FROM users WHERE EMAIL=? AND PASSWORD=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $email, $pass);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) == 1) {
            $_SESSION['logged_in'] = true;
            $_SESSION['email'] = $email;
            header("Location: cardetails.php");
            exit();
        } else {
            $error = "Invalid email or password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaRs - Premium Car Rental Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        :root {
            --primary-color: #4158D0;
            --secondary-color: #C850C0;
            --text-color: #333;
            --light-text: #666;
            --white: #fff;
            --transition: all 0.3s ease;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: var(--transition);
        }

        .navbar.scrolled {
            padding: 15px 50px;
            background: rgba(255, 255, 255, 0.98);
        }

        .logo {
            font-size: 2em;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo i {
            color: var(--secondary-color);
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            transition: var(--transition);
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            transition: var(--transition);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .admin-btn {
            padding: 8px 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: var(--transition);
        }

        .admin-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--text-color);
            font-size: 1.5em;
            cursor: pointer;
            transition: var(--transition);
        }

        .mobile-menu-btn:hover {
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
            }

            .navbar.scrolled {
                padding: 10px 20px;
            }

            .mobile-menu-btn {
                display: block;
            }

            .nav-links {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(10px);
                flex-direction: column;
                justify-content: flex-start;
                padding-top: 50px;
                transition: var(--transition);
            }

            .nav-links.active {
                left: 0;
            }

            .nav-links a {
                font-size: 1.2em;
            }

            .admin-btn {
                margin-top: 20px;
            }
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 100px 50px;
            background: url('images/carbg5.jpg') center/cover;
            position: relative;
            gap: 30px;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0,0,0,0.8), rgba(0,0,0,0.5));
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 600px;
            color: white;
        }

        .hero-content h1 {
            font-size: clamp(2.5em, 5vw, 4em);
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: clamp(1em, 2vw, 1.2em);
            margin-bottom: 30px;
            line-height: 1.6;
            opacity: 0.9;
        }

        .login-form {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
        }

        .login-form h2 {
            color: var(--text-color);
            margin-bottom: 30px;
            text-align: center;
            font-size: clamp(1.5em, 3vw, 2em);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 5px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(65, 88, 208, 0.1);
            outline: none;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            color: var(--light-text);
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 1024px) {
            .hero {
                padding: 100px 30px;
            }
        }

        @media (max-width: 768px) {
            .hero {
                flex-direction: column;
                padding: 120px 20px 50px;
                text-align: center;
                min-height: auto;
            }

            .hero-content {
                margin-bottom: 40px;
            }

            .login-form {
                padding: 30px 20px;
                margin: 0 auto;
            }

            .form-group input {
                padding: 10px 12px;
            }
        }

        @media (max-width: 480px) {
            .hero {
                padding: 100px 15px 30px;
            }

            .hero-content h1 {
                font-size: 2em;
            }

            .hero-content p {
                font-size: 1em;
            }

            .login-form {
                padding: 25px 15px;
            }
        }

        /* Features Section */
        .features {
            padding: 80px 50px;
            background: #f8f9fa;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .feature-card i {
            font-size: 2.5em;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .feature-card h3 {
            margin-bottom: 15px;
            color: var(--text-color);
        }

        .feature-card p {
            color: var(--light-text);
            line-height: 1.6;
        }

        /* How It Works Section */
        .how-it-works {
            padding: 80px 20px;
            background-color: #f9f9f9;
            text-align: center;
        }
        
        .how-it-works h2 {
            font-size: 2.5rem;
            margin-bottom: 50px;
            color: var(--text-color);
        }
        
        .steps-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .step {
            flex: 1;
            min-width: 220px;
            margin: 20px;
            padding: 30px 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .step:hover {
            transform: translateY(-10px);
        }
        
        .step-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .step-icon i {
            font-size: 30px;
            color: white;
        }
        
        .step h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--text-color);
        }
        
        .step p {
            color: var(--light-text);
            line-height: 1.6;
        }

        /* Popular Cars Section */
        .popular-cars {
            padding: 80px 20px;
            text-align: center;
        }
        
        .popular-cars h2 {
            font-size: 2.5rem;
            margin-bottom: 50px;
            color: var(--text-color);
        }
        
        .cars-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
            gap: 30px;
        }
        
        .car-item {
            flex: 1;
            min-width: 300px;
            max-width: 350px;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .car-item:hover {
            transform: translateY(-10px);
        }
        
        .car-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .car-details {
            padding: 20px;
        }
        
        .car-details h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--text-color);
        }
        
        .car-features {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .car-features span {
            display: flex;
            align-items: center;
            color: var(--light-text);
            font-size: 0.9rem;
        }
        
        .car-features i {
            margin-right: 5px;
            color: var(--primary-color);
        }
        
        .car-price {
            margin-bottom: 20px;
        }
        
        .car-price span {
            display: block;
            color: var(--light-text);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .car-price h4 {
            font-size: 1.3rem;
            color: var(--primary-color);
        }
        
        .view-btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        
        .view-btn:hover {
            background: linear-gradient(135deg, #3a4bc0, #7a4bc0);
        }

        /* Testimonials Section */
        .testimonials {
            padding: 80px 20px;
            background-color: #f9f9f9;
            text-align: center;
        }
        
        .testimonials h2 {
            font-size: 2.5rem;
            margin-bottom: 50px;
            color: var(--text-color);
        }
        
        .testimonials-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
            gap: 30px;
        }
        
        .testimonial {
            flex: 1;
            min-width: 300px;
            max-width: 350px;
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .testimonial-content {
            margin-bottom: 20px;
        }
        
        .testimonial-content p {
            color: var(--light-text);
            line-height: 1.6;
            font-style: italic;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        
        .testimonial-author img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }
        
        .testimonial-author h4 {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: var(--text-color);
        }
        
        .testimonial-author span {
            color: var(--light-text);
            font-size: 0.9rem;
        }

        /* CTA Section */
        .cta-section {
            padding: 100px 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            text-align: center;
            color: white;
        }
        
        .cta-content {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .cta-content h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .cta-content p {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        
        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .secondary-btn {
            display: inline-block;
            padding: 15px 30px;
            background-color: transparent;
            color: white;
            border: 2px solid white;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .secondary-btn:hover {
            background-color: white;
            color: var(--primary-color);
        }

        /* Footer */
        footer {
            background-color: #222;
            color: white;
            padding: 60px 20px 20px;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
            gap: 40px;
        }
        
        .footer-logo h2 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .footer-logo p {
            color: #aaa;
        }
        
        .footer-links h3, .footer-contact h3, .footer-social h3 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-links h3:after, .footer-contact h3:after, .footer-social h3:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 2px;
            background-color: var(--primary-color);
        }
        
        .footer-links ul {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: #aaa;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .footer-contact p {
            margin-bottom: 10px;
            color: #aaa;
            display: flex;
            align-items: center;
        }
        
        .footer-contact i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        .footer-social {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .social-icons {
            display: flex;
            gap: 15px;
        }
        
        .social-icons a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: #333;
            border-radius: 50%;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .social-icons a:hover {
            background-color: var(--primary-color);
            transform: translateY(-5px);
        }
        
        .footer-bottom {
            max-width: 1200px;
            margin: 40px auto 0;
            padding-top: 20px;
            border-top: 1px solid #444;
            text-align: center;
            color: #aaa;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .steps-container, .cars-container, .testimonials-container {
                flex-direction: column;
                align-items: center;
            }
            
            .step, .car-item, .testimonial {
                max-width: 100%;
            }
            
            .footer-content {
                flex-direction: column;
                gap: 30px;
            }
            
            .cta-content h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">
            <i class="fas fa-car"></i>
            CaRs
        </a>
        <button class="mobile-menu-btn">
            <i class="fas fa-bars"></i>
        </button>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="aboutus.html">About</a>
            <a href="services.php">Services</a>
            <a href="contactus.html">Contact</a>
            <a href="adminlogin.php" class="admin-btn">Admin Login</a>
        </div>
    </nav>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Mobile menu toggle
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const navLinks = document.querySelector('.nav-links');
        
        mobileMenuBtn.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            const icon = this.querySelector('i');
            if (navLinks.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });

        // Close mobile menu when clicking a link
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('active');
                const icon = mobileMenuBtn.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            });
        });
    </script>

    <section class="hero">
        <div class="hero-content">
            <h1>Rent Your Dream Car</h1>
            <p>Experience luxury and comfort with our premium car rental service. Choose from our vast collection of well-maintained vehicles and make every journey memorable.</p>
            <a href="register.php" class="cta-btn">Join Us Today</a>
        </div>

        <div class="login-form">
            <h2>Welcome Back</h2>
            <form method="POST">
                <?php if(isset($error)): ?>
                    <div style="color: #dc3545; margin-bottom: 15px; text-align: center; padding: 10px; background: rgba(220, 53, 69, 0.1); border-radius: 5px;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>
                <div class="form-group">
                    <input type="password" name="pass" placeholder="Password" required>
                </div>
                <button type="submit" name="login" class="login-btn">Login</button>
                <div class="register-link">
                    Don't have an account? <a href="register.php">Sign up</a>
                </div>
            </form>
        </div>
    </section>

    <section class="features">
        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-car"></i>
                <h3>Premium Vehicles</h3>
                <p>Choose from our extensive collection of well-maintained, luxury vehicles for your perfect journey.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-shield-alt"></i>
                <h3>Safe & Secure</h3>
                <p>All our vehicles are regularly maintained and thoroughly sanitized for your safety.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-clock"></i>
                <h3>24/7 Support</h3>
                <p>Our customer support team is always ready to assist you with any queries or concerns.</p>
            </div>
        </div>
    </section>

    <section class="how-it-works">
        <h2>How It Works</h2>
        <div class="steps-container">
            <div class="step">
                <div class="step-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Find Your Car</h3>
                <p>Browse our extensive collection of premium vehicles and choose the one that suits your needs.</p>
            </div>
            <div class="step">
                <div class="step-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3>Book Your Dates</h3>
                <p>Select your pickup and return dates, and check real-time availability.</p>
            </div>
            <div class="step">
                <div class="step-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h3>Make Payment</h3>
                <p>Secure payment options including credit card, PayPal, UPI, and bank transfer.</p>
            </div>
            <div class="step">
                <div class="step-icon">
                    <i class="fas fa-car"></i>
                </div>
                <h3>Enjoy Your Ride</h3>
                <p>Pick up your car and enjoy a comfortable, hassle-free journey.</p>
            </div>
        </div>
    </section>

    <section class="popular-cars">
        <h2>Our Popular Models</h2>
        <div class="cars-container">
            <div class="car-item">
                <img src="images/IMG-67c2c4fd07b179.70845872.webp" alt="Luxury Sedan">
                <div class="car-details">
                    <h3>Luxury Sedan</h3>
                    <div class="car-features">
                        <span><i class="fas fa-gas-pump"></i> Petrol</span>
                        <span><i class="fas fa-users"></i> 5 Seater</span>
                        <span><i class="fas fa-cog"></i> Automatic</span>
                    </div>
                    <div class="car-price">
                        <span>Starting from</span>
                        <h4>₹5,000/day</h4>
                    </div>
                    <a href="cardetails.php" class="view-btn">View Details</a>
                </div>
            </div>
            <div class="car-item">
                <img src="images/IMG-67c2c602a5a7d4.68241319.webp" alt="Premium SUV">
                <div class="car-details">
                    <h3>Premium SUV</h3>
                    <div class="car-features">
                        <span><i class="fas fa-gas-pump"></i> Diesel</span>
                        <span><i class="fas fa-users"></i> 7 Seater</span>
                        <span><i class="fas fa-cog"></i> Automatic</span>
                    </div>
                    <div class="car-price">
                        <span>Starting from</span>
                        <h4>₹7,500/day</h4>
                    </div>
                    <a href="cardetails.php" class="view-btn">View Details</a>
                </div>
            </div>
            <div class="car-item">
                <img src="images/IMG-67c2c67a2d1052.11495314.webp" alt="Electric Car">
                <div class="car-details">
                    <h3>Electric Car</h3>
                    <div class="car-features">
                        <span><i class="fas fa-bolt"></i> Electric</span>
                        <span><i class="fas fa-users"></i> 5 Seater</span>
                        <span><i class="fas fa-cog"></i> Automatic</span>
                    </div>
                    <div class="car-price">
                        <span>Starting from</span>
                        <h4>₹6,000/day</h4>
                    </div>
                    <a href="cardetails.php" class="view-btn">View Details</a>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonials">
        <h2>What Our Customers Say</h2>
        <div class="testimonials-container">
            <div class="testimonial">
                <div class="testimonial-content">
                    <p>"The booking process was incredibly smooth, and the car was in perfect condition. Highly recommend CaRs for anyone looking for a reliable rental service!"</p>
                </div>
                <div class="testimonial-author">
                    <img src="images/IMG-67c2caf8e059b2.00292983.webp" alt="User">
                    <div>
                        <h4>Rahul Sharma</h4>
                        <span>Delhi</span>
                    </div>
                </div>
            </div>
            <div class="testimonial">
                <div class="testimonial-content">
                    <p>"I needed a car for a business trip, and CaRs delivered beyond my expectations. The vehicle was clean, well-maintained, and the customer service was exceptional."</p>
                </div>
                <div class="testimonial-author">
                    <img src="images/IMG-67c2cb45a621f9.79040169.webp" alt="User">
                    <div>
                        <h4>Priya Patel</h4>
                        <span>Mumbai</span>
                    </div>
                </div>
            </div>
            <div class="testimonial">
                <div class="testimonial-content">
                    <p>"The date-based availability feature made it so easy to find a car for my exact travel dates. The UPI payment option was also very convenient. Will definitely use CaRs again!"</p>
                </div>
                <div class="testimonial-author">
                    <img src="images/IMG-67c2cb85178245.06930293.webp" alt="User">
                    <div>
                        <h4>Amit Verma</h4>
                        <span>Bangalore</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="cta-content">
            <h2>Ready to Experience Premium Car Rental?</h2>
            <p>Join thousands of satisfied customers and make your journey memorable with CaRs.</p>
            <div class="cta-buttons">
                <a href="register.php" class="cta-btn" style="display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; text-decoration: none; border-radius: 5px; font-weight: 600; margin-right: 15px;">Start Your Journey</a>
                <a href="gallery.php" class="cta-btn" style="display: inline-block; padding: 15px 30px; background: transparent; color: white; text-decoration: none; border-radius: 5px; font-weight: 600; border: 2px solid white;">Explore Our Fleet</a>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <h2>CaRs</h2>
                <p>Premium Car Rental Service</p>
            </div>
            <div class="footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="aboutus.html">About Us</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="contactus.html">Contact Us</a></li>
                    <li><a href="cardetails.php">Cars</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h3>Contact Us</h3>
                <p><i class="fas fa-map-marker-alt"></i> 123 Car Street, Katpadi, India</p>
                <p><i class="fas fa-phone"></i> +91 81246 76002</p>
                <p><i class="fas fa-phone"></i> +91 93420 90144</p>
                <p><i class="fas fa-envelope"></i> onlinecarrental@gmail.com</p>
            </div>
            <div class="footer-social">
                <h3>Follow Us</h3>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> CaRs. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>
