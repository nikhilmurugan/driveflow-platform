<?php
require 'connection.php';
if (!isset($_SESSION)) {
    session_start();
}

// Check if user is logged in as admin
if (!isset($_SESSION['admin_login'])) {
    header("Location: adminlogin.php");
    exit();
}

// Check if email is provided
if (!isset($_GET['id'])) {
    header("Location: adminusers.php");
    exit();
}

$email = mysqli_real_escape_string($conn, $_GET['id']);

// Get user details
$sql = "SELECT * FROM users WHERE EMAIL = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: adminusers.php");
    exit();
}

$user = $result->fetch_assoc();

// Get user's booking history
$sql = "SELECT b.*, c.CAR_NAME, c.CAR_IMG 
        FROM booking b 
        LEFT JOIN cars c ON b.CAR_ID = c.CAR_ID 
        WHERE b.EMAIL = ? 
        ORDER BY b.BOOK_DATE DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$bookings = $stmt->get_result();

// Calculate statistics
$total_bookings = $bookings->num_rows;
$total_spent = 0;
$completed_bookings = 0;
$pending_bookings = 0;

if ($total_bookings > 0) {
    $bookings_data = [];
    while ($booking = $bookings->fetch_assoc()) {
        $bookings_data[] = $booking;
        if ($booking['BOOK_STATUS'] === 'APPROVED') {
            $total_spent += $booking['PRICE'];
            $completed_bookings++;
        } elseif ($booking['BOOK_STATUS'] === 'PENDING') {
            $pending_bookings++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User - CaRs Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f5f5f5;
            min-height: 100vh;
        }

        .navbar {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
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
            font-weight: 600;
            color: #4158D0;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
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

        .container {
            max-width: 1200px;
            margin: 6rem auto 2rem;
            padding: 0 1rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            color: #333;
            font-size: 2rem;
        }

        .user-profile {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: #4158D0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: 600;
        }

        .profile-info h2 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .profile-info p {
            color: #666;
            margin-bottom: 0.25rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
        }

        .stat-card h3 {
            color: #4158D0;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            color: #666;
            font-size: 0.875rem;
        }

        .bookings-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .bookings-section h2 {
            color: #333;
            margin-bottom: 1.5rem;
        }

        .booking-card {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .car-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .car-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }

        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-approved {
            background: #28a745;
            color: white;
        }

        .status-pending {
            background: #ffc107;
            color: #000;
        }

        .status-rejected {
            background: #dc3545;
            color: white;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-secondary {
            background: #666;
            color: white;
        }

        .btn-secondary:hover {
            background: #555;
        }

        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }

            .container {
                margin-top: 8rem;
            }

            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .booking-header {
                flex-direction: column;
                gap: 1rem;
            }

            .car-info {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="admindash.php" class="logo">CaRs Admin</a>
            <div class="nav-links">
                <a href="admindash.php">Dashboard</a>
                <a href="adminusers.php">Users</a>
                <a href="adminvehicle.php">Vehicles</a>
                <a href="adminbook.php">Bookings</a>
                <a href="admincontactus.php">Messages</a>
                <a href="index.php" class="logout-btn" style="background-color: #ff4444; color: white; padding: 0.5rem 1rem; border-radius: 5px; text-decoration: none; transition: all 0.3s ease;">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-user"></i> User Profile</h1>
            <a href="adminusers.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>

        <div class="user-profile">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($user['FNAME'], 0, 1)); ?>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($user['FNAME'] . ' ' . $user['LNAME']); ?></h2>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['EMAIL']); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['PHONE_NUMBER']); ?></p>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $total_bookings; ?></h3>
                    <p>Total Bookings</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $completed_bookings; ?></h3>
                    <p>Completed Bookings</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $pending_bookings; ?></h3>
                    <p>Pending Bookings</p>
                </div>
                <div class="stat-card">
                    <h3>₹<?php echo number_format($total_spent); ?></h3>
                    <p>Total Spent</p>
                </div>
            </div>
        </div>

        <div class="bookings-section">
            <h2><i class="fas fa-history"></i> Booking History</h2>
            <?php if (isset($bookings_data) && !empty($bookings_data)): ?>
                <?php foreach ($bookings_data as $booking): ?>
                    <div class="booking-card">
                        <div class="booking-header">
                            <div class="car-info">
                                <img src="images/<?php echo htmlspecialchars($booking['CAR_IMG']); ?>" alt="<?php echo htmlspecialchars($booking['CAR_NAME']); ?>" class="car-image">
                                <div>
                                    <h3><?php echo htmlspecialchars($booking['CAR_NAME']); ?></h3>
                                    <p>Booking ID: <?php echo $booking['BOOK_ID']; ?></p>
                                </div>
                            </div>
                            <span class="status-badge status-<?php echo strtolower($booking['BOOK_STATUS']); ?>">
                                <?php echo $booking['BOOK_STATUS']; ?>
                            </span>
                        </div>
                        <div class="booking-details">
                            <div>
                                <p><strong>From:</strong> <?php echo date('M d, Y', strtotime($booking['BOOK_DATE'])); ?></p>
                                <p><strong>To:</strong> <?php echo date('M d, Y', strtotime($booking['RETURN_DATE'])); ?></p>
                            </div>
                            <div>
                                <p><strong>Duration:</strong> <?php echo $booking['DURATION']; ?> days</p>
                                <p><strong>Price:</strong> ₹<?php echo number_format($booking['PRICE']); ?></p>
                            </div>
                            <div>
                                <p><strong>Pickup:</strong> <?php echo htmlspecialchars($booking['BOOK_PLACE']); ?></p>
                                <p><strong>Destination:</strong> <?php echo htmlspecialchars($booking['DESTINATION']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No booking history found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
