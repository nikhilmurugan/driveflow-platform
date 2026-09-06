<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - CaRs</title>
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f5;
            padding: 2rem;
        }

        .success-container {
            background: white;
            border-radius: 10px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #4CAF50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: scaleIn 0.5s ease-out;
        }

        .success-icon i {
            color: white;
            font-size: 40px;
        }

        h1 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 2rem;
        }

        p {
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #4158D0;
            color: white;
        }

        .btn-primary:hover {
            background: #3448a5;
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e2e6ea;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .booking-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 2rem 0;
            text-align: left;
        }

        .booking-info h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            color: #666;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-item strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h1>Payment Successful!</h1>
        <p>Thank you for your payment. Your car booking has been confirmed.</p>
        
        <?php if(isset($_GET['booking_id'])): ?>
        <div class="booking-info">
            <h3>Booking Details</h3>
            <div class="info-item">
                <span>Booking ID:</span>
                <strong>#<?php echo htmlspecialchars($_GET['booking_id']); ?></strong>
            </div>
            <div class="info-item">
                <span>Status:</span>
                <strong>Confirmed</strong>
            </div>
        </div>
        <?php endif; ?>

        <div class="buttons">
            <a href="cardetails.php" class="btn btn-primary">Return to Home</a>
            <a href="bookinstatus.php" class="btn btn-secondary">View Booking</a>
        </div>
    </div>
</body>
</html>
