<?php
/* Basic Navbar Component */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .navbar {
            background: #fff;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo {
            color: #4158D0;
            font-size: 1.8rem;
            font-weight: 700;
            text-decoration: none;
        }

        .nav-menu {
            list-style: none;
            display: flex;
            gap: 1rem;
            margin: 0;
            padding: 0;
        }

        .nav-menu li {
            display: inline;
        }

        .nav-menu li a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }

        /* Responsive styles if needed */
        @media (max-width: 768px) {
            .nav-menu {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo"><i class="fas fa-car"></i> CaRs</a>
            <ul class="nav-menu">
                <li><a href="bookinstatus.php">My Bookings</a></li>
                <li><a href="cardetails.php">Cars</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
</body>
</html>
