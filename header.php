<?php
if (!isset($_SESSION)) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

require_once 'connection.php';

// Get user's name from database
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT FNAME FROM users WHERE EMAIL = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userName = $user ? $user['FNAME'] : 'Account';

// Get current page for active link highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'CaRs - Car Rental System'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f8f9fa;
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
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
        }

        /* Container for page content */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        /* Add your additional styling if needed */
    </style>
    <?php if (isset($extraStyles)) echo $extraStyles; ?>
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
                <a href="cardetails.php" class="nav-link <?php echo ($currentPage == 'cardetails.php') ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    Home
                </a>
                <a href="aboutus2.php" class="nav-link <?php echo ($currentPage == 'aboutus2.php') ? 'active' : ''; ?>">
                    <i class="fas fa-info-circle"></i>
                    About
                </a>
                <a href="contactus2.php" class="nav-link <?php echo ($currentPage == 'contactus2.php') ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i>
                    Contact
                </a>
                <a href="bookinstatus.php" class="nav-link <?php echo ($currentPage == 'bookinstatus.php') ? 'active' : ''; ?>">
                    <i class="fas fa-bookmark"></i>
                    My Bookings
                </a>
                <a href="feedback.php" class="nav-link <?php echo ($currentPage == 'feedback.php') ? 'active' : ''; ?>">
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
</body>
</html>