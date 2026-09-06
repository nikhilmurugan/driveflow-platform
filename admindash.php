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

// Initialize stats array with default values
$stats = array(
    'users' => 0,
    'cars' => 0,
    'bookings' => 0,
    'pending_bookings' => 0,
    'revenue' => 0
);

// Safely execute queries with error handling
function safeQuery($conn, $query) {
    try {
        $result = $conn->query($query);
        return $result;
    } catch (mysqli_sql_exception $e) {
        // Log error or handle it silently
        return false;
    }
}

// Total Users
$result = safeQuery($conn, "SELECT COUNT(*) as count FROM users");
if ($result && $row = $result->fetch_assoc()) {
    $stats['users'] = $row['count'];
}

// Total Cars
$result = safeQuery($conn, "SELECT COUNT(*) as count FROM cars");
if ($result && $row = $result->fetch_assoc()) {
    $stats['cars'] = $row['count'];
}

// Total Bookings
$result = safeQuery($conn, "SELECT COUNT(*) as count FROM booking");
if ($result && $row = $result->fetch_assoc()) {
    $stats['bookings'] = $row['count'];
}

// Pending Bookings
$result = safeQuery($conn, "SELECT COUNT(*) as count FROM booking WHERE BOOK_STATUS = 'PENDING'");
if ($result && $row = $result->fetch_assoc()) {
    $stats['pending_bookings'] = $row['count'];
}

// Total Revenue
$result = safeQuery($conn, "SELECT SUM(PRICE) as total FROM booking WHERE BOOK_STATUS = 'APPROVED'");
if ($result && $row = $result->fetch_assoc()) {
    $stats['revenue'] = $row['total'] ?? 0;
}

// Recent Bookings
$recent_bookings = safeQuery($conn, "
    SELECT b.*, c.CAR_NAME, CONCAT(u.FNAME, ' ', u.LNAME) as USER_NAME 
    FROM booking b 
    LEFT JOIN cars c ON b.CAR_ID = c.CAR_ID 
    LEFT JOIN users u ON b.EMAIL = u.EMAIL 
    ORDER BY b.BOOK_DATE DESC LIMIT 5
");

// Recent Messages
$recent_messages = safeQuery($conn, "
    SELECT name, email, subject, message, submission_date, status 
    FROM contacts 
    ORDER BY submission_date DESC 
    LIMIT 5
");

// Recent Feedbacks
$feedbackQuery = "SELECT f.*, u.FNAME, u.LNAME, f.rating, f.comment, f.feedback_date 
                FROM feedback f 
                JOIN users u ON f.email = u.EMAIL 
                ORDER BY f.feedback_date DESC 
                LIMIT 5";
$recent_feedbacks = $conn->query($feedbackQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CaRs Rental System</title>
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
            background: #f4f6f9;
            color: #1a1a1a;
            line-height: 1.6;
        }

        .navbar {
            background: #fff;
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
            font-weight: 700;
            color: #1a1a1a;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: #1a1a1a;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #007bff;
        }

        .container {
            max-width: 1200px;
            margin: 100px auto 2rem;
            padding: 0 2rem;
        }

        .dashboard-header {
            margin-bottom: 2rem;
        }

        .dashboard-header h1 {
            font-size: 2rem;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            background: #007bff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .stat-card .stat-icon i {
            color: #fff;
            font-size: 1.5rem;
        }

        .stat-card h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            color: #666;
            font-size: 0.875rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .dashboard-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .dashboard-card h2 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dashboard-card h2 i {
            color: #007bff;
        }

        .recent-item {
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .recent-item:last-child {
            border-bottom: none;
        }

        .recent-item h4 {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .recent-item p {
            color: #666;
            font-size: 0.875rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-pending {
            background: #ffc107;
            color: #000;
        }

        .status-approved {
            background: #28a745;
            color: #fff;
        }

        .status-rejected {
            background: #dc3545;
            color: #fff;
        }

        .quick-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.3s ease;
        }

        .action-btn:hover {
            background: #0056b3;
        }

        /* Feedback Styles */
        .feedback-item {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: var(--text-color);
        }

        .feedback-date {
            font-size: 0.9em;
            color: var(--light-text);
        }

        .rating {
            color: #ffd700;
        }

        .rating i {
            margin-left: 2px;
        }

        .rating i.active {
            color: #ffd700;
        }

        .feedback-text {
            color: var(--text-color);
            line-height: 1.6;
        }

        .no-data {
            text-align: center;
            color: var(--light-text);
            padding: 20px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
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

    <!-- Main Content -->
    <div class="container">
        <div class="dashboard-header">
            <h1>Dashboard Overview</h1>
            <p>Welcome back, Admin!</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3><?php echo number_format($stats['users']); ?></h3>
                <p>Total Users</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-car"></i>
                </div>
                <h3><?php echo number_format($stats['cars']); ?></h3>
                <p>Available Cars</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3><?php echo number_format($stats['bookings']); ?></h3>
                <p>Total Bookings</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h3>$<?php echo number_format($stats['revenue'], 2); ?></h3>
                <p>Total Revenue</p>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Recent Bookings -->
            <div class="dashboard-card">
                <h2><i class="fas fa-clock"></i> Recent Bookings</h2>
                <?php if ($recent_bookings && $recent_bookings->num_rows > 0): ?>
                    <?php while($booking = $recent_bookings->fetch_assoc()): ?>
                        <div class="recent-item">
                            <h4><?php echo htmlspecialchars($booking['USER_NAME']); ?></h4>
                            <p>
                                <i class="fas fa-car"></i> <?php echo htmlspecialchars($booking['CAR_NAME']); ?><br>
                                <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($booking['BOOK_DATE'])); ?>
                            </p>
                            <span class="status-badge status-<?php echo strtolower($booking['BOOK_STATUS'] ?? 'pending'); ?>">
                                <?php echo htmlspecialchars($booking['BOOK_STATUS'] ?? 'PENDING'); ?>
                            </span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No recent bookings found.</p>
                <?php endif; ?>
                <div class="quick-actions">
                    <a href="adminbook.php" class="action-btn">
                        <i class="fas fa-list"></i> View All Bookings
                    </a>
                </div>
            </div>

            <!-- Recent Messages -->
            <div class="dashboard-card">
                <h2><i class="fas fa-envelope"></i> Recent Messages</h2>
                <?php if ($recent_messages && $recent_messages->num_rows > 0): ?>
                    <?php while($message = $recent_messages->fetch_assoc()): ?>
                        <div class="recent-item">
                            <h4><?php echo htmlspecialchars($message['name']); ?></h4>
                            <p>
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($message['email']); ?><br>
                                <i class="fas fa-comment"></i> <?php echo htmlspecialchars(substr($message['message'], 0, 100) . '...'); ?>
                            </p>
                            <?php if (isset($message['status'])): ?>
                                <span class="status-badge status-<?php echo strtolower($message['status']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($message['status'])); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No recent messages found.</p>
                <?php endif; ?>
                <div class="quick-actions">
                    <a href="admincontactus.php" class="action-btn">
                        <i class="fas fa-inbox"></i> View All Messages
                    </a>
                </div>
            </div>

            <!-- Recent Feedbacks -->
            <div class="dashboard-card">
                <h2><i class="fas fa-comment"></i> Recent Feedbacks</h2>
                <?php if ($recent_feedbacks && $recent_feedbacks->num_rows > 0): ?>
                    <?php while($feedback = $recent_feedbacks->fetch_assoc()): ?>
                        <div class="feedback-item">
                            <div class="feedback-header">
                                <div class="user-info">
                                    <span class="user-name"><?php echo htmlspecialchars($feedback['FNAME'] . ' ' . $feedback['LNAME']); ?></span>
                                    <span class="feedback-date"><?php echo date('M j, Y', strtotime($feedback['feedback_date'])); ?></span>
                                </div>
                                <div class="rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?php echo ($i <= $feedback['rating'] ? '' : '-o'); ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="feedback-text"><?php echo htmlspecialchars($feedback['comment']); ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-data">No feedback available</p>
                <?php endif; ?>
                <div class="quick-actions">
                    <a href="adminfeedback.php" class="action-btn">
                        <i class="fas fa-comment"></i> View All Feedbacks
                    </a>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="dashboard-card">
                <h2><i class="fas fa-chart-bar"></i> Quick Stats</h2>
                <div class="recent-item">
                    <h4>Pending Bookings</h4>
                    <p>
                        <i class="fas fa-clock"></i> <?php echo $stats['pending_bookings']; ?> bookings awaiting approval
                    </p>
                </div>
                <div class="recent-item">
                    <h4>Today's Overview</h4>
                    <p>
                        <i class="fas fa-calendar-day"></i> <?php echo date('l, F j, Y'); ?>
                    </p>
                </div>
                <div class="quick-actions">
                    <a href="adminvehicle.php" class="action-btn">
                        <i class="fas fa-car"></i> Manage Vehicles
                    </a>
                    <a href="adminusers.php" class="action-btn">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>