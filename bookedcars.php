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

// Check if car_id is provided
if (!isset($_GET['car_id']) || empty($_GET['car_id'])) {
    header('location: bookinstatus.php');
    exit();
}

$car_id = $_GET['car_id'];

// Fetch car details
$stmt = $conn->prepare("SELECT * FROM cars WHERE CAR_ID = ?");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Car not found
    header('location: bookinstatus.php');
    exit();
}

$car = $result->fetch_assoc();

// Get booking details for this car by this user
$stmt = $conn->prepare("SELECT * FROM booking WHERE CAR_ID = ? AND EMAIL = ? ORDER BY BOOK_DATE DESC LIMIT 1");
$stmt->bind_param("is", $car_id, $email);
$stmt->execute();
$bookingResult = $stmt->get_result();
$booking = $bookingResult->num_rows > 0 ? $bookingResult->fetch_assoc() : null;

// Page title
$pageTitle = $car['CAR_NAME'] . " - Details";

// Extra styles
$extraStyles = <<<EOT
<style>
    .car-detail-header {
        background: linear-gradient(rgba(65, 88, 208, 0.8), rgba(200, 80, 192, 0.8)), url('images/carbg2.jpg');
        background-size: cover;
        background-position: center;
        padding: 6rem 0 4rem;
        color: white;
        text-align: center;
        position: relative;
    }

    .car-detail-header::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 80px;
        background: linear-gradient(to top, #f8f9fa, transparent);
    }

    .car-detail-header h1 {
        font-size: 2.8rem;
        margin-bottom: 1rem;
        font-weight: 700;
        position: relative;
        z-index: 1;
    }

    .car-detail-header p {
        font-size: 1.2rem;
        max-width: 700px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .car-detail-container {
        padding: 4rem 0;
        background: #f8f9fa;
    }

    .car-detail-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        overflow: hidden;
        margin-bottom: 2rem;
        transition: var(--transition);
        border: 1px solid rgba(0,0,0,0.05);
    }

    .car-image-gallery {
        width: 100%;
        padding: 2rem;
        text-align: center;
    }

    .car-image-gallery img {
        max-width: 100%;
        height: auto;
        max-height: 350px;
        border-radius: var(--border-radius);
        object-fit: contain;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }

    .car-info-section {
        padding: 2rem;
        border-top: 1px solid #f5f5f5;
    }

    .car-name {
        font-size: 2.2rem;
        margin-bottom: 1rem;
        color: var(--dark-color);
        font-weight: 700;
    }

    .car-price {
        font-size: 1.8rem;
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
    }

    .price-per-day {
        margin-left: 0.5rem;
        font-size: 1rem;
        color: #777;
        font-weight: 400;
    }

    .car-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .car-detail-item {
        display: flex;
        flex-direction: column;
    }

    .car-detail-label {
        font-size: 0.9rem;
        color: #777;
        margin-bottom: 0.3rem;
    }

    .car-detail-value {
        font-size: 1.1rem;
        color: var(--dark-color);
        font-weight: 500;
    }

    .car-action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 2rem;
    }

    .action-btn {
        padding: 0.8rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: var(--transition);
        font-size: 1rem;
    }

    .btn-primary {
        background: var(--primary-gradient);
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(45deg, #3448a5, #b346ad);
        transform: translateY(-3px);
    }

    .btn-outline {
        background: transparent;
        color: var(--dark-color);
        border: 1px solid #ddd;
    }

    .btn-outline:hover {
        background: #f5f5f5;
        transform: translateY(-3px);
    }

    .booking-details {
        margin-top: 3rem;
        padding: 2rem;
        background: #f9f9f9;
        border-radius: var(--border-radius);
        border: 1px solid #eee;
    }

    .booking-details h3 {
        margin-bottom: 1.5rem;
        font-size: 1.5rem;
        color: var(--dark-color);
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .car-action-buttons {
            flex-direction: column;
        }
        
        .action-btn {
            width: 100%;
            justify-content: center;
        }
        
        .car-detail-header h1 {
            font-size: 2rem;
        }
    }
</style>
EOT;

include 'header.php';
?>

<section class="car-detail-header">
    <div class="container">
        <h1><?php echo htmlspecialchars($car['CAR_NAME']); ?></h1>
        <p>Detailed information about your booked car</p>
    </div>
</section>

<div class="car-detail-container">
    <div class="container">
        <div class="car-detail-card">
            <div class="car-image-gallery">
                <?php
                $imagePath = 'images/' . $car['CAR_IMG'];
                $imageSrc = file_exists($imagePath) && !empty($car['CAR_IMG']) ? $imagePath : 'images/carbg.jpg';
                ?>
                <img src="<?php echo $imageSrc; ?>" alt="<?php echo htmlspecialchars($car['CAR_NAME']); ?>">
            </div>
            
            <div class="car-info-section">
                <h2 class="car-name"><?php echo htmlspecialchars($car['CAR_NAME']); ?></h2>
                
                <div class="car-price">
                    $<?php echo number_format($car['PRICE'], 2); ?>
                    <span class="price-per-day">per day</span>
                </div>
                
                <div class="car-details-grid">
                    <div class="car-detail-item">
                        <div class="car-detail-label">Fuel Type</div>
                        <div class="car-detail-value">
                            <i class="fas fa-gas-pump"></i> 
                            <?php echo htmlspecialchars($car['FUEL_TYPE']); ?>
                        </div>
                    </div>
                    
                    <div class="car-detail-item">
                        <div class="car-detail-label">Capacity</div>
                        <div class="car-detail-value">
                            <i class="fas fa-user-friends"></i> 
                            <?php echo $car['CAPACITY']; ?> persons
                        </div>
                    </div>
                    
                    <div class="car-detail-item">
                        <div class="car-detail-label">Availability</div>
                        <div class="car-detail-value">
                            <i class="fas fa-<?php echo $car['AVAILABLE'] === 'YES' || $car['AVAILABLE'] === 'Y' ? 'check-circle' : 'times-circle'; ?>"></i> 
                            <?php echo $car['AVAILABLE'] === 'YES' || $car['AVAILABLE'] === 'Y' ? 'Available' : 'Not Available'; ?>
                        </div>
                    </div>
                </div>
                
                <div class="car-action-buttons">
                    <a href="bookinstatus.php" class="action-btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to My Bookings
                    </a>
                    
                    <?php if ($car['AVAILABLE'] === 'YES' || $car['AVAILABLE'] === 'Y'): ?>
                    <a href="book.php?id=<?php echo $car['CAR_ID']; ?>" class="action-btn btn-primary">
                        <i class="fas fa-calendar-plus"></i> Book Again
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($booking): ?>
            <div class="car-info-section booking-details">
                <h3>Your Booking Details</h3>
                
                <div class="car-details-grid">
                    <div class="car-detail-item">
                        <div class="car-detail-label">Booking ID</div>
                        <div class="car-detail-value">
                            <i class="fas fa-ticket-alt"></i> 
                            #<?php echo $booking['BOOK_ID']; ?>
                        </div>
                    </div>
                    
                    <div class="car-detail-item">
                        <div class="car-detail-label">Status</div>
                        <div class="car-detail-value">
                            <i class="fas fa-info-circle"></i> 
                            <?php echo $booking['BOOK_STATUS']; ?>
                        </div>
                    </div>
                    
                    <div class="car-detail-item">
                        <div class="car-detail-label">Pick-up Location</div>
                        <div class="car-detail-value">
                            <i class="fas fa-map-marker-alt"></i> 
                            <?php echo htmlspecialchars($booking['BOOK_PLACE']); ?>
                        </div>
                    </div>
                    
                    <div class="car-detail-item">
                        <div class="car-detail-label">Destination</div>
                        <div class="car-detail-value">
                            <i class="fas fa-map-marked-alt"></i> 
                            <?php echo htmlspecialchars($booking['DESTINATION']); ?>
                        </div>
                    </div>
                    
                    <div class="car-detail-item">
                        <div class="car-detail-label">Start Date</div>
                        <div class="car-detail-value">
                            <i class="far fa-calendar-alt"></i> 
                            <?php echo date('d M Y', strtotime($booking['BOOK_DATE'])); ?>
                        </div>
                    </div>
                    
                    <div class="car-detail-item">
                        <div class="car-detail-label">Return Date</div>
                        <div class="car-detail-value">
                            <i class="far fa-calendar-alt"></i> 
                            <?php echo date('d M Y', strtotime($booking['RETURN_DATE'])); ?>
                        </div>
                    </div>
                    
                    <div class="car-detail-item">
                        <div class="car-detail-label">Duration</div>
                        <div class="car-detail-value">
                            <i class="far fa-clock"></i> 
                            <?php echo $booking['DURATION']; ?> days
                        </div>
                    </div>
                    
                    <div class="car-detail-item">
                        <div class="car-detail-label">Total Price</div>
                        <div class="car-detail-value">
                            <i class="fas fa-money-bill-wave"></i> 
                            $<?php echo number_format($booking['PRICE'], 2); ?>
                        </div>
                    </div>
                    
                    <div class="car-detail-item">
                        <div class="car-detail-label">Payment Method</div>
                        <div class="car-detail-value">
                            <i class="fas fa-credit-card"></i> 
                            Cash
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 