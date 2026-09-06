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

// Handle booking status updates
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $action = mysqli_real_escape_string($conn, $_GET['action']);
    
    if ($action === 'approve') {
        // Start transaction to ensure data consistency
        $conn->begin_transaction();
        
        try {
            // Get the booking details
            $getBookingSql = "SELECT CAR_ID, BOOK_DATE, RETURN_DATE FROM booking WHERE BOOK_ID = ?";
            $getBookingStmt = $conn->prepare($getBookingSql);
            $getBookingStmt->bind_param("i", $id);
            $getBookingStmt->execute();
            $bookingResult = $getBookingStmt->get_result();
            $bookingData = $bookingResult->fetch_assoc();
            $carId = $bookingData['CAR_ID'];
            $bookDate = $bookingData['BOOK_DATE'];
            $returnDate = $bookingData['RETURN_DATE'];
            $getBookingStmt->close();
            
            // Approve this booking
            $approveSql = "UPDATE booking SET BOOK_STATUS = 'APPROVED' WHERE BOOK_ID = ?";
            $approveStmt = $conn->prepare($approveSql);
            $approveStmt->bind_param("i", $id);
            $approveStmt->execute();
            $approveStmt->close();
            
            // Reject all other pending bookings for the same car that overlap with this date range
            $rejectOthersSql = "UPDATE booking SET BOOK_STATUS = 'REJECTED' 
                               WHERE CAR_ID = ? 
                               AND BOOK_ID != ? 
                               AND BOOK_STATUS = 'PENDING'
                               AND (
                                   (BOOK_DATE <= ? AND RETURN_DATE >= ?) OR 
                                   (BOOK_DATE <= ? AND RETURN_DATE >= ?) OR
                                   (BOOK_DATE >= ? AND RETURN_DATE <= ?)
                               )";
            $rejectOthersStmt = $conn->prepare($rejectOthersSql);
            $rejectOthersStmt->bind_param("iissssss", $carId, $id, $returnDate, $bookDate, $bookDate, $bookDate, $bookDate, $returnDate);
            $rejectOthersStmt->execute();
            $rejectOthersStmt->close();
            
            // Commit the transaction
            $conn->commit();
            
        } catch (Exception $e) {
            // An error occurred; rollback the transaction
            $conn->rollback();
            die("Error processing booking: " . $e->getMessage());
        }
    } elseif ($action === 'reject') {
        $sql = "UPDATE booking SET BOOK_STATUS = 'REJECTED' WHERE BOOK_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'delete') {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Get the booking details
            $getBookingSql = "SELECT CAR_ID, BOOK_STATUS FROM booking WHERE BOOK_ID = ?";
            $getBookingStmt = $conn->prepare($getBookingSql);
            $getBookingStmt->bind_param("i", $id);
            $getBookingStmt->execute();
            $bookingResult = $getBookingStmt->get_result();
            $bookingData = $bookingResult->fetch_assoc();
            $carId = $bookingData['CAR_ID'];
            $bookStatus = $bookingData['BOOK_STATUS'];
            $getBookingStmt->close();
            
            // Delete the booking
            $deleteSql = "DELETE FROM booking WHERE BOOK_ID = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("i", $id);
            $deleteStmt->execute();
            $deleteStmt->close();
            
            // Commit the transaction
            $conn->commit();
        } catch (Exception $e) {
            // An error occurred; rollback the transaction
            $conn->rollback();
            die("Error deleting booking: " . $e->getMessage());
        }
    }
    
    header("Location: adminbook.php");
    exit();
}

// Fetch all bookings with car and user details
$sql = "SELECT b.*, c.CAR_NAME, CONCAT(u.FNAME, ' ', u.LNAME) as USER_NAME, u.EMAIL as USER_EMAIL, u.PHONE_NUMBER as USER_PHONE 
        FROM booking b 
        LEFT JOIN cars c ON b.CAR_ID = c.CAR_ID 
        LEFT JOIN users u ON b.EMAIL = u.EMAIL 
        ORDER BY b.BOOK_DATE DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Error fetching bookings: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Booking Management</title>
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

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 2rem;
            color: #1a1a1a;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: #fff;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .search-box input {
            border: none;
            outline: none;
            padding: 0.5rem;
            font-size: 1rem;
            width: 300px;
        }

        .search-box i {
            color: #666;
            margin-right: 0.5rem;
        }

        .bookings-table {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            width: 100%;
        }

        .bookings-table th,
        .bookings-table td {
            padding: 1rem;
            text-align: left;
        }

        .bookings-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #1a1a1a;
        }

        .bookings-table tr:not(:last-child) {
            border-bottom: 1px solid #eee;
        }

        .bookings-table tbody tr:hover {
            background: #f8f9fa;
        }

        .booking-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
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

        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.3s ease;
            margin: 0 0.25rem;
            display: inline-block;
        }

        .approve-btn {
            background: #28a745;
            color: #fff;
        }

        .approve-btn:hover {
            background: #218838;
        }

        .reject-btn {
            background: #dc3545;
            color: #fff;
        }

        .reject-btn:hover {
            background: #c82333;
        }

        .delete-btn {
            background: #6c757d;
            color: #fff;
        }

        .delete-btn:hover {
            background: #5a6268;
        }

        .user-info {
            color: #666;
        }

        .car-name {
            font-weight: 500;
            color: #007bff;
        }

        .date-info {
            color: #666;
            font-size: 0.875rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }

            .header {
                flex-direction: column;
                gap: 1rem;
            }

            .search-box {
                width: 100%;
            }

            .search-box input {
                width: 100%;
            }

            .bookings-table {
                display: block;
                overflow-x: auto;
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
        <div class="header">
            <h1>Booking Management</h1>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search bookings..." onkeyup="searchBookings()">
            </div>
        </div>

        <table class="bookings-table">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Car</th>
                    <th>Customer</th>
                    <th>Booking Details</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $row['BOOK_ID']; ?></td>
                        <td class="car-name">
                            <i class="fas fa-car"></i> <?php echo $row['CAR_NAME']; ?>
                        </td>
                        <td class="user-info">
                            <div><i class="fas fa-user"></i> <?php echo $row['USER_NAME']; ?></div>
                            <div><i class="fas fa-envelope"></i> <?php echo $row['USER_EMAIL']; ?></div>
                            <div><i class="fas fa-phone"></i> <?php echo $row['USER_PHONE']; ?></div>
                        </td>
                        <td class="date-info">
                            <div><i class="fas fa-calendar-alt"></i> From: <?php echo date('M d, Y', strtotime($row['BOOK_DATE'])); ?></div>
                            <div><i class="fas fa-calendar-check"></i> To: <?php echo date('M d, Y', strtotime($row['RETURN_DATE'])); ?></div>
                            <div><i class="fas fa-clock"></i> Duration: <?php echo $row['DURATION']; ?> days</div>
                        </td>
                        <td>
                            <strong>$<?php echo number_format($row['PRICE'], 2); ?></strong>
                        </td>
                        <td>
                            <span class="booking-status status-<?php echo strtolower($row['BOOK_STATUS'] ?? 'pending'); ?>">
                                <?php echo $row['BOOK_STATUS'] ?? 'PENDING'; ?>
                            </span>
                        </td>
                        <td>
                            <?php if (($row['BOOK_STATUS'] ?? 'PENDING') === 'PENDING'): ?>
                                <a href="adminbook.php?action=approve&id=<?php echo $row['BOOK_ID']; ?>" 
                                   class="action-btn approve-btn"
                                   onclick="return confirm('Are you sure you want to approve this booking?')">
                                    <i class="fas fa-check"></i> Approve
                                </a>
                                <a href="adminbook.php?action=reject&id=<?php echo $row['BOOK_ID']; ?>" 
                                   class="action-btn reject-btn"
                                   onclick="return confirm('Are you sure you want to reject this booking?')">
                                    <i class="fas fa-times"></i> Reject
                                </a>
                            <?php endif; ?>
                            <a href="adminbook.php?action=delete&id=<?php echo $row['BOOK_ID']; ?>" 
                               class="action-btn delete-btn"
                               onclick="return confirm('Are you sure you want to delete this booking? This action cannot be undone.')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        function searchBookings() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.querySelector('.bookings-table');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const carCol = rows[i].getElementsByTagName('td')[1];
                const userCol = rows[i].getElementsByTagName('td')[2];
                if (carCol && userCol) {
                    const car = carCol.textContent || carCol.innerText;
                    const user = userCol.textContent || userCol.innerText;
                    if (car.toLowerCase().indexOf(filter) > -1 || user.toLowerCase().indexOf(filter) > -1) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            }
        }
    </script>
</body>
</html>