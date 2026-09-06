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

// Process contact form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message_content = $_POST['message'];
    
    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message_content)) {
        $message = '<div class="alert alert-danger">Please fill in all fields.</div>';
    } else {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $name, $email, $subject, $message_content);
        
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Your message has been sent successfully. We will get back to you soon!</div>';
        } else {
            $message = '<div class="alert alert-danger">Sorry, there was an error sending your message. Please try again later.</div>';
        }
    }
}

// Set page title
$pageTitle = "Contact Us - CaRs";

// Add extra styles specific to this page
$extraStyles = <<<EOT
<style>
    .contact-hero {
        background: linear-gradient(rgba(65, 88, 208, 0.8), rgba(200, 80, 192, 0.8)), url('images/carbg2.jpg');
        background-size: cover;
        background-position: center;
        height: 50vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .contact-hero::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 80px;
        background: linear-gradient(to top, #f8f9fa, transparent);
    }

    .contact-hero-content {
        max-width: 800px;
        padding: 0 2rem;
        position: relative;
        z-index: 1;
    }

    .contact-hero-content h1 {
        font-size: 3.5rem;
        margin-bottom: 1.5rem;
        font-weight: 700;
        letter-spacing: 1px;
        text-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }

    .contact-hero-content p {
        font-size: 1.3rem;
        max-width: 700px;
        margin: 0 auto 2rem;
        font-weight: 300;
        line-height: 1.6;
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

    .contact-container {
        padding: 5rem 0;
        background: #f8f9fa;
    }

    .contact-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 3rem;
    }

    .contact-form {
        background: white;
        padding: 2.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
    }

    .form-title {
        font-size: 1.8rem;
        color: var(--dark-color);
        margin-bottom: 1.5rem;
        position: relative;
        padding-bottom: 10px;
    }

    .form-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background: var(--primary-gradient);
        border-radius: 2px;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--dark-color);
        font-weight: 500;
    }

    .form-control {
        width: 100%;
        padding: 0.8rem 1rem;
        border: 1px solid #e0e0e0;
        border-radius: var(--border-radius);
        font-family: inherit;
        font-size: 1rem;
        color: var(--dark-color);
        transition: var(--transition);
    }

    .form-control:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(65, 88, 208, 0.1);
    }

    textarea.form-control {
        min-height: 150px;
        resize: vertical;
    }

    .submit-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.8rem;
        padding: 0.8rem 2rem;
        background: var(--primary-gradient);
        color: #fff;
        border: none;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--transition);
        font-weight: 500;
        font-size: 1rem;
        box-shadow: 0 4px 15px rgba(65, 88, 208, 0.3);
    }

    .submit-btn:hover {
        background: linear-gradient(45deg, #3448a5, #b346ad);
        transform: translateY(-3px);
    }

    .contact-info {
        background: white;
        padding: 2.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
    }

    .info-title {
        font-size: 1.8rem;
        color: var(--dark-color);
        margin-bottom: 1.5rem;
        position: relative;
        padding-bottom: 10px;
    }

    .info-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background: var(--primary-gradient);
        border-radius: 2px;
    }

    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .info-icon {
        width: 40px;
        height: 40px;
        background: rgba(65, 88, 208, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
        flex-shrink: 0;
    }

    .info-content h4 {
        font-size: 1.1rem;
        color: var(--dark-color);
        margin-bottom: 0.3rem;
    }

    .info-content p, .info-content a {
        color: var(--text-color);
        font-size: 1rem;
        line-height: 1.7;
        text-decoration: none;
        transition: var(--transition);
    }

    .info-content a:hover {
        color: var(--primary-color);
    }

    .social-links {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
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
    
    .map-container {
        margin-top: 3rem;
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--box-shadow);
    }
    
    .map-container iframe {
        width: 100%;
        height: 400px;
        border: 0;
    }
    
    .alert {
        padding: 1rem;
        border-radius: var(--border-radius);
        margin-bottom: 1.5rem;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    @media (max-width: 768px) {
        .contact-content {
            grid-template-columns: 1fr;
        }
        
        .contact-hero-content h1 {
            font-size: 2.5rem;
        }
        
        .contact-hero-content p {
            font-size: 1.1rem;
        }
    }
</style>
EOT;

// Include header
include 'header.php';
?>

<section class="contact-hero">
    <div class="contact-hero-content">
        <h1>Contact Us</h1>
        <p>Have questions or need assistance? We're here to help. Reach out to our team and we'll get back to you as soon as possible.</p>
    </div>
</section>

<div class="contact-container">
    <div class="container">
        <div class="section-header">
            <h2>Get In Touch</h2>
            <p>We'd love to hear from you. Our friendly team is always here to chat and answer any questions you might have.</p>
        </div>
        
        <?php echo $message; ?>
        
        <div class="contact-content">
            <div class="contact-form">
                <h3 class="form-title">Send Us a Message</h3>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="name" class="form-label">Your Name</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Your Email</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" id="subject" name="subject" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="message" class="form-label">Message</label>
                        <textarea id="message" name="message" class="form-control" required></textarea>
                    </div>
                    <button type="submit" name="submit_contact" class="submit-btn">
                        <i class="fas fa-paper-plane"></i>
                        Send Message
                    </button>
                </form>
            </div>
            
            <div class="contact-info">
                <h3 class="info-title">Contact Information</h3>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <h4>Location</h4>
                        <p>123 Car Street, Automobile City, AC 12345</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="info-content">
                        <h4>Phone</h4>
                        <a href="tel:+1234567890">+1 (234) 567-890</a>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <h4>Email</h4>
                        <a href="mailto:info@carrental.com">info@carrental.com</a>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-content">
                        <h4>Hours</h4>
                        <p>Monday - Friday: 9am - 6pm<br>Saturday: 10am - 4pm<br>Sunday: Closed</p>
                    </div>
                </div>
                
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
        
        <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d387193.3059445135!2d-74.25986613799748!3d40.69714941887875!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2sin!4v1634667964306!5m2!1sen!2sin" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
</div>

<?php
include 'footer.php';
?>