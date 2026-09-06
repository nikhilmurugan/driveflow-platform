<?php
session_start();
include('connection.php');

// Redirect if not logged in
if (!isset($_SESSION['email'])) {
    header('location: login.php');
    exit();
}

// Get user's name from database
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT FNAME FROM users WHERE EMAIL = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userName = $user ? $user['FNAME'] : 'Account';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - CaRs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        :root {
            --primary-color: #4158D0;
            --primary-light: #C850C0;
            --primary-gradient: linear-gradient(45deg, #4158D0, #C850C0);
            --secondary-color: #ffc107;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --light-color: #f5f5f5;
            --dark-color: #333;
            --text-color: #555;
            --border-radius: 10px;
            --box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        body {
            background: #f8f9fa;
            color: var(--text-color);
            line-height: 1.6;
        }

        .navbar {
            background: #fff;
            padding: 1rem 2rem;
            box-shadow: var(--box-shadow);
            position: sticky;
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
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.8rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo i {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hamburger {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark-color);
            cursor: pointer;
            padding: 0.5rem;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-link {
            color: var(--dark-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            padding: 0.5rem 0;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary-color);
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary-gradient);
            border-radius: 3px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(65, 88, 208, 0.05);
            padding: 0.5rem 1rem;
            border-radius: 50px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-light);
        }

        .user-name {
            font-weight: 600;
            color: var(--dark-color);
        }

        .logout-btn {
            color: var(--danger-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            color: #bd2130;
        }

        @media (max-width: 768px) {
            .hamburger {
                display: block;
            }

            .nav-menu {
                position: fixed;
                top: 80px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 80px);
                flex-direction: column;
                background: #fff;
                padding: 2rem 1rem;
                transition: 0.3s ease-in-out;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                z-index: 20;
            }

            .nav-menu.active {
                left: 0;
            }

            .nav-link {
                font-size: 1.2rem;
                padding: 1rem 0;
                width: 100%;
                text-align: center;
                border-bottom: 1px solid #eee;
            }

            .nav-link.active::after {
                display: none;
            }

            .user-profile {
                flex-direction: column;
                text-align: center;
                padding: 1rem 0;
                background: none;
            }

            .logout-btn {
                padding: 1rem 0;
                width: 100%;
                text-align: center;
                border-top: 1px solid #eee;
                margin-top: 1rem;
                justify-content: center;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .section-header h2 {
                font-size: 2rem;
            }

            .milestone-count {
                font-size: 2.5rem;
            }

            .testimonial-item {
                padding: 2rem 1.5rem;
            }

            .cta-content {
                padding: 3rem 1.5rem;
            }

            .cta h2 {
                font-size: 2rem;
            }
        }

        .hero {
            background: linear-gradient(rgba(65, 88, 208, 0.8), rgba(200, 80, 192, 0.8)), url('images/carbg2.jpg');
            background-size: cover;
            background-position: center;
            height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: linear-gradient(to top, #f8f9fa, transparent);
        }

        .hero-content {
            max-width: 800px;
            padding: 0 2rem;
            position: relative;
            z-index: 1;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .hero-content p {
            font-size: 1.3rem;
            max-width: 700px;
            margin: 0 auto 2rem;
            font-weight: 300;
            line-height: 1.6;
        }

        .hero-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1rem 2rem;
            background: var(--primary-gradient);
            color: #fff;
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: var(--transition);
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(65, 88, 208, 0.3);
        }

        .hero-btn:hover {
            background: linear-gradient(45deg, #3448a5, #b346ad);
            transform: translateY(-3px);
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }

        .section-header h2 {
            font-size: 2.5rem;
            color: var(--dark-color);
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .section-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 70px;
            height: 4px;
            background: var(--primary-gradient);
            border-radius: 2px;
        }

        .section-header p {
            max-width: 700px;
            margin: 0 auto;
            color: var(--text-color);
            font-size: 1.1rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 6rem 1rem;
        }

        .about-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .about-card {
            background: white;
            padding: 2.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.05);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .about-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .about-card i {
            font-size: 3rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
        }

        .about-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--dark-color);
            font-weight: 600;
        }

        .about-card p {
            color: var(--text-color);
            font-size: 1rem;
            line-height: 1.7;
        }

        .features {
            background: #fff;
            padding: 6rem 0;
            position: relative;
        }

        .features::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: linear-gradient(to bottom, #f8f9fa, transparent);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 1.5rem;
            transition: var(--transition);
            padding: 1.5rem;
            border-radius: var(--border-radius);
        }

        .feature-item:hover {
            background: rgba(65, 88, 208, 0.05);
            transform: translateY(-5px);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(65, 88, 208, 0.3);
            flex-shrink: 0;
        }

        .feature-icon i {
            font-size: 1.5rem;
            color: white;
        }

        .feature-content {
            flex-grow: 1;
        }

        .feature-content h4 {
            font-size: 1.3rem;
            margin-bottom: 0.8rem;
            color: var(--dark-color);
            font-weight: 600;
        }

        .feature-content p {
            color: var(--text-color);
            font-size: 1rem;
            line-height: 1.7;
        }

        .team {
            padding: 6rem 0;
            background: #f8f9fa;
            position: relative;
        }

        .team::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: linear-gradient(to bottom, #fff, transparent);
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2.5rem;
            margin-top: 3rem;
        }

        .team-member {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .team-member:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .member-image {
            position: relative;
            height: 280px;
            overflow: hidden;
        }

        .member-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .team-member:hover .member-image img {
            transform: scale(1.05);
        }

        .member-info {
            padding: 1.5rem;
            text-align: center;
        }

        .member-info h3 {
            font-size: 1.5rem;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .member-info p {
            color: var(--primary-color);
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .member-bio {
            color: var(--text-color);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f5f5f5;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: var(--transition);
        }

        .social-links a:hover {
            background: var(--primary-gradient);
            color: white;
            transform: translateY(-3px);
        }

        .milestones {
            background: linear-gradient(rgba(65, 88, 208, 0.9), rgba(200, 80, 192, 0.9)), url('images/carbg2.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 6rem 0;
            color: white;
            text-align: center;
        }

        .milestones-header {
            margin-bottom: 4rem;
        }

        .milestones-header h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .milestones-header p {
            max-width: 700px;
            margin: 0 auto;
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .milestones-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .milestone-item {
            position: relative;
        }

        .milestone-item i {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            opacity: 0.9;
        }

        .milestone-count {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .milestone-text {
            font-size: 1.1rem;
            font-weight: 300;
            opacity: 0.9;
        }

        .testimonials {
            padding: 6rem 0;
            background: #fff;
        }

        .testimonial-slider {
            max-width: 800px;
            margin: 3rem auto 0;
            position: relative;
        }

        .testimonial-item {
            background: #f8f9fa;
            padding: 3rem;
            border-radius: var(--border-radius);
            text-align: center;
            position: relative;
            margin: 2rem 1rem;
            box-shadow: var(--box-shadow);
        }

        .testimonial-item::before {
            content: '"';
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 5rem;
            color: rgba(65, 88, 208, 0.1);
            font-family: 'Georgia', serif;
            line-height: 1;
        }

        .testimonial-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-color);
            margin-bottom: 2rem;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .author-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .author-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .author-info h4 {
            font-size: 1.1rem;
            color: var(--dark-color);
            margin-bottom: 0.3rem;
        }

        .author-info p {
            color: var(--primary-color);
            font-size: 0.9rem;
        }

        .cta {
            background: #f8f9fa;
            padding: 6rem 0;
            text-align: center;
        }

        .cta-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .cta-content {
            background: white;
            padding: 4rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            position: relative;
            overflow: hidden;
        }

        .cta-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--primary-gradient);
        }

        .cta h2 {
            font-size: 2.5rem;
            color: var(--dark-color);
            margin-bottom: 1.5rem;
        }

        .cta p {
            font-size: 1.1rem;
            color: var(--text-color);
            max-width: 600px;
            margin: 0 auto 2rem;
            line-height: 1.6;
        }

        .cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1rem 2.5rem;
            background: var(--primary-gradient);
            color: #fff;
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: var(--transition);
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(65, 88, 208, 0.3);
            font-size: 1.1rem;
        }

        .cta-btn:hover {
            background: linear-gradient(45deg, #3448a5, #b346ad);
            transform: translateY(-3px);
        }

        .footer {
            background: var(--dark-color);
            padding: 4rem 0 2rem;
            color: white;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
        }

        .footer-col h3 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .footer-col h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background: var(--primary-gradient);
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.8rem;
        }

        .footer-links a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-links a i {
            font-size: 0.8rem;
        }

        .footer-links a:hover {
            color: white;
            transform: translateX(5px);
        }

        .footer-social {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .footer-social a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-social a:hover {
            background: var(--primary-gradient);
            transform: translateY(-3px);
        }

        .copyright {
            text-align: center;
            padding-top: 3rem;
            margin-top: 3rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.7);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<nav class="navbar">
        <div class="nav-container">
            <a href="cardetails.php" class="logo">
                <i class="fas fa-car"></i>
                CaRs
            </a>
            
            <button class="hamburger" id="hamburger">
                <i class="fas fa-bars"></i>
            </button>

            <div class="nav-menu" id="nav-menu">
                <a href="cardetails.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    Home
                </a>
                <a href="aboutus2.php" class="nav-link active">
                    <i class="fas fa-info-circle"></i>
                    About
                </a>
                <a href="contactus2.php" class="nav-link">
                    <i class="fas fa-envelope"></i>
                    Contact
                </a>
                <a href="bookinstatus.php" class="nav-link">
                    <i class="fas fa-bookmark"></i>
                    My Bookings
                </a>
                <a href="feedback.php" class="nav-link">
                    <i class="fas fa-comment"></i>
                    Feedback
                </a>
                <div class="user-profile">
                    <img src="images/profile.png" alt="Profile" class="user-avatar">
                    <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-content">
            <h1>About CaRs</h1>
            <p>Your trusted partner in car rentals, providing quality vehicles and exceptional service since 2023</p>
            <a href="cardetails.php" class="hero-btn">
                <i class="fas fa-car"></i>
                Browse Our Fleet
            </a>
        </div>
    </section>

    <div class="container">
        <div class="section-header">
            <h2>Our Mission</h2>
            <p>At CaRs, we're committed to providing you with the best rental experience possible through quality vehicles and exceptional service.</p>
        </div>
        <div class="about-grid">
            <div class="about-card">
                <i class="fas fa-car"></i>
                <h3>Quality Vehicles</h3>
                <p>We maintain a fleet of well-maintained, modern vehicles to ensure your comfort and safety on every journey. Our cars undergo regular inspections and servicing to meet the highest standards.</p>
            </div>
            <div class="about-card">
                <i class="fas fa-handshake"></i>
                <h3>Customer Service</h3>
                <p>Our dedicated team is committed to providing exceptional service and support throughout your rental experience. We're always available to assist with any questions or concerns you may have.</p>
            </div>
            <div class="about-card">
                <i class="fas fa-shield-alt"></i>
                <h3>Safe & Reliable</h3>
                <p>All our vehicles undergo regular maintenance and safety checks to ensure worry-free travel. Your safety is our priority, which is why we never compromise on the quality of our fleet.</p>
            </div>
        </div>
    </div>

    <section class="features">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose Us</h2>
                <p>Discover the advantages that make CaRs the preferred choice for car rentals</p>
            </div>
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="feature-content">
                        <h4>24/7 Support</h4>
                        <p>Our customer support team is available around the clock to assist you with any queries or issues, ensuring peace of mind throughout your rental period.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="feature-content">
                        <h4>Best Rates</h4>
                        <p>We offer competitive pricing with no hidden charges, ensuring you get the best value for your money. Our transparent pricing policy means no surprises at checkout.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <div class="feature-content">
                        <h4>Flexible Pickup</h4>
                        <p>With multiple convenient pickup and drop-off locations, we make it easy for you to start and end your journey at locations that suit your travel plans.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="feature-content">
                        <h4>Easy Booking</h4>
                        <p>Our simple and quick online booking process allows you to reserve your desired vehicle in minutes, saving you time and ensuring a hassle-free experience.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="milestones">
        <div class="milestones-header">
            <h2>Our Achievements</h2>
            <p>Numbers that reflect our commitment to excellence and customer satisfaction</p>
        </div>
        <div class="milestones-grid">
            <div class="milestone-item">
                <i class="fas fa-car"></i>
                <div class="milestone-count">100+</div>
                <div class="milestone-text">Premium Vehicles</div>
            </div>
            <div class="milestone-item">
                <i class="fas fa-users"></i>
                <div class="milestone-count">2,500+</div>
                <div class="milestone-text">Happy Customers</div>
            </div>
            <div class="milestone-item">
                <i class="fas fa-map-marker-alt"></i>
                <div class="milestone-count">12</div>
                <div class="milestone-text">Locations</div>
            </div>
            <div class="milestone-item">
                <i class="fas fa-trophy"></i>
                <div class="milestone-count">98%</div>
                <div class="milestone-text">Satisfaction Rate</div>
            </div>
        </div>
    </section>

    <section class="team">
        <div class="container">
            <div class="section-header">
                <h2>Meet Our Team</h2>
                <p>The dedicated professionals behind CaRs who work tirelessly to provide you with exceptional service</p>
            </div>
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-image">
                        <img src="images/profile.png" alt="Team Member">
                    </div>
                    <div class="member-info">
                        <h3>Althaf Ahmed.H</h3>
                        <p>Founder & CEO</p>
                        <div class="member-bio">
                            With over 15 years of experience in the automotive industry, Althaf leads our team with passion and innovation.
                        </div>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-facebook"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <div class="member-image">
                        <img src="images/profile.png" alt="Team Member">
                    </div>
                    <div class="member-info">
                        <h3>Nikhil Murugan.D.P</h3>
                        <p>Operations Manager</p>
                        <div class="member-bio">
                            Nikhil ensures smooth daily operations and maintains our fleet to the highest standards of quality and safety.
                        </div>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-facebook"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <div class="member-image">
                        <img src="images/profile.png" alt="Team Member">
                    </div>
                    <div class="member-info">
                        <h3>Mike Johnson</h3>
                        <p>Customer Service Head</p>
                        <div class="member-bio">
                            Mike leads our customer service team, ensuring that every client receives prompt and helpful assistance.
                        </div>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-facebook"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonials">
        <div class="container">
            <div class="section-header">
                <h2>What Our Customers Say</h2>
                <p>Hear from our satisfied customers about their experience with CaRs</p>
            </div>
            <div class="testimonial-slider">
                <div class="testimonial-item">
                    <p class="testimonial-text">
                        "CaRs provided me with an excellent rental experience. The vehicle was clean, well-maintained, and the customer service was exceptional. I'll definitely be using their services again for my future trips."
                    </p>
                    <div class="testimonial-author">
                        <div class="author-image">
                            <img src="images/profile.png" alt="Customer">
                        </div>
                        <div class="author-info">
                            <h4>John Doe</h4>
                            <p>Regular Customer</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="cta-container">
            <div class="cta-content">
                <h2>Ready to Hit the Road?</h2>
                <p>Experience the convenience and quality of CaRs. Browse our fleet and book your perfect vehicle today for your next adventure.</p>
                <a href="cardetails.php" class="cta-btn">
                    <i class="fas fa-car"></i>
                    Explore Our Fleet
                </a>
            </div>
        </div>
    </section>

    <?php
include 'footer.php';
?>

    <script>
        // Toggle mobile menu
        const hamburger = document.getElementById('hamburger');
        const navMenu = document.getElementById('nav-menu');

        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            // Change hamburger icon
            const icon = hamburger.querySelector('i');
            if (navMenu.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
                const icon = hamburger.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });

        // Close menu when clicking a link
        document.querySelectorAll('.nav-link, .logout-btn').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    navMenu.classList.remove('active');
                    const icon = hamburger.querySelector('i');
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
        });

        // Animate milestone counters
        document.addEventListener('DOMContentLoaded', function() {
            const milestoneElements = document.querySelectorAll('.milestone-count');
            
            function isInViewport(element) {
                const rect = element.getBoundingClientRect();
                return (
                    rect.top >= 0 &&
                    rect.left >= 0 &&
                    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                    rect.right <= (window.innerWidth || document.documentElement.clientWidth)
                );
            }
            
            function animateCounters() {
                milestoneElements.forEach(milestone => {
                    if (isInViewport(milestone) && !milestone.classList.contains('animated')) {
                        milestone.classList.add('animated');
                        const target = parseInt(milestone.textContent.replace(/,|\+/g, ''));
                        let count = 0;
                        const duration = 2000; // 2 seconds
                        const interval = Math.ceil(duration / target);
                        
                        const counter = setInterval(() => {
                            count += Math.ceil(target / (duration / 20));
                            if (count >= target) {
                                count = target;
                                clearInterval(counter);
                            }
                            
                            let displayText = count.toString();
                            if (milestone.textContent.includes('+')) {
                                displayText += '+';
                            }
                            
                            milestone.textContent = displayText;
                        }, interval);
                    }
                });
            }
            
            window.addEventListener('scroll', animateCounters);
            animateCounters(); // Run once on page load
        });
    </script>
</body>
</html>
