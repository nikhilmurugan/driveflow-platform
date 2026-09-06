<?php
require 'connection.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid booking ID");
}

$bookingId = $_GET['id'];
$email = $_SESSION['email'];

$query = "SELECT b.*, c.CAR_NAME, c.CAR_IMG, c.FUEL_TYPE, c.CAPACITY, c.PRICE, u.FNAME, u.LNAME, u.PHONE_NUMBER FROM booking b JOIN cars c ON b.CAR_ID = c.CAR_ID JOIN users u ON b.EMAIL = u.EMAIL WHERE b.`BOOK_ID` = ? AND b.`EMAIL` = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("is", $bookingId, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Booking not found or unauthorized access");
}

$booking = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - CaRs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #e8e8e8;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 2rem;
        }
        .booking-details {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
        }
        .car-info, .booking-info {
            flex: 1 1 400px;
        }
        .car-image img {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        .detail-item {
            background: #f7f9fc;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        h2, h3 {
            color: #3498db;
            margin-top: 0;
        }
        p {
            margin: 0.5rem 0;
        }
        .status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-confirmed { background: #2ecc71; color: white; }
        .status-pending { background: #f39c12; color: white; }
        .status-cancelled { background: #e74c3c; color: white; }
    </style>
</head>
<body>
    <?php include(__DIR__.'/navbar.php'); ?>

    <div class="container">
        <h1>Booking Details</h1>
        
        <div class="booking-details">
            <div class="car-info">
                <div class="car-image">
                    <img src="images/<?= htmlspecialchars($booking['CAR_IMG']) ?>" alt="<?= htmlspecialchars($booking['CAR_NAME']) ?>">
                </div>
                <div class="detail-item">
                    <h2><?= htmlspecialchars($booking['CAR_NAME']) ?></h2>
                    <p><i class="fas fa-gas-pump"></i> <?= htmlspecialchars($booking['FUEL_TYPE']) ?></p>
                    <p><i class="fas fa-users"></i> <?= htmlspecialchars($booking['CAPACITY']) ?> seats</p>
                    <p><i class="fas fa-rupee-sign"></i> <?= number_format($booking['PRICE'], 2) ?>/day</p>
                </div>
            </div>

            <div class="booking-info">
                <div class="detail-item">
                    <h3><i class="far fa-calendar-alt"></i> Booking Dates</h3>
                    <p>Pickup: <?= date('M j, Y', strtotime($booking['BOOK_DATE'])) ?></p>
                    <p>Return: <?= date('M j, Y', strtotime($booking['RETURN_DATE'])) ?></p>
                    <p>Total Days: <?= $booking['DURATION'] ?></p>
                </div>

                <div class="detail-item">
                    <h3><i class="far fa-user"></i> User Details</h3>
                    <p><?= htmlspecialchars($booking['FNAME'] . ' ' . $booking['LNAME']) ?></p>
                    <p><i class="fas fa-phone"></i> <?= htmlspecialchars($booking['PHONE_NUMBER']) ?></p>
                </div>

                <div class="detail-item">
                    <h3><i class="fas fa-money-check-alt"></i> Payment Details</h3>
                    <p>Total Amount: <strong>₹<?= number_format($booking['PRICE'], 2) ?></strong></p>
                    <p>Status: <span class="status status-<?= strtolower($booking['BOOK_STATUS']) ?>"><?= htmlspecialchars($booking['BOOK_STATUS']) ?></span></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
