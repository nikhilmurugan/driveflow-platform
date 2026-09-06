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

// Handle booking cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking']) && isset($_POST['booking_id'])) {
    $bookingId = $_POST['booking_id'];
    $userEmail = $_SESSION['email'];
    
    // Update booking status to CANCELLED
    $stmt = $conn->prepare("UPDATE booking SET STATUS = 'CANCELLED' WHERE BOOKING_ID = ? AND EMAIL = ?");
    $stmt->bind_param("is", $bookingId, $userEmail);
    
    if ($stmt->execute()) {
        $cancelMessage = '<div class="alert alert-success">Booking #' . $bookingId . ' has been successfully cancelled.</div>';
    } else {
        $cancelMessage = '<div class="alert alert-danger">Failed to cancel booking. Please try again later.</div>';
    }
}

// Fetch active bookings (not cancelled)
$activeBookingsQuery = "SELECT b.*, c.CAR_NAME, c.PRICE, c.CAR_ID, c.CAR_IMG 
                       FROM booking b 
                       JOIN cars c ON b.CAR_ID = c.CAR_ID 
                       WHERE b.EMAIL = ? AND b.BOOK_STATUS != 'CANCELLED'
                       ORDER BY b.BOOK_DATE DESC";
$stmt = $conn->prepare($activeBookingsQuery);
$stmt->bind_param("s", $email);
$stmt->execute();
$activeBookings = $stmt->get_result();

// Fetch cancelled bookings
$cancelledBookingsQuery = "SELECT b.*, c.CAR_NAME, c.PRICE, c.CAR_ID, c.CAR_IMG 
                          FROM booking b 
                          JOIN cars c ON b.CAR_ID = c.CAR_ID 
                          WHERE b.EMAIL = ? AND b.BOOK_STATUS = 'CANCELLED'
                          ORDER BY b.BOOK_DATE DESC";
$stmt = $conn->prepare($cancelledBookingsQuery);
$stmt->bind_param("s", $email);
$stmt->execute();
$cancelledBookings = $stmt->get_result();

// Set page title
$pageTitle = "My Bookings - CaRs";

// Add extra styles specific to this page
$extraStyles = <<<EOT
<style>
    .booking-header {
        background: linear-gradient(rgba(65, 88, 208, 0.8), rgba(200, 80, 192, 0.8)), url('images/carbg2.jpg');
        background-size: cover;
        background-position: center;
        padding: 6rem 0 4rem;
        color: white;
        text-align: center;
        position: relative;
    }

    .booking-header::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 80px;
        background: linear-gradient(to top, #f8f9fa, transparent);
    }

    .booking-header h1 {
        font-size: 2.8rem;
        margin-bottom: 1rem;
        font-weight: 700;
        position: relative;
        z-index: 1;
    }

    .booking-header p {
        font-size: 1.2rem;
        max-width: 700px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .booking-container {
        padding: 4rem 0;
        background: #f8f9fa;
    }

    .booking-tabs {
            display: flex;
        margin-bottom: 2rem;
        justify-content: center;
        gap: 1rem;
    }

    .booking-tab {
        padding: 0.8rem 2rem;
        background: white;
        border-radius: var(--border-radius);
        cursor: pointer;
            font-weight: 500;
        color: var(--dark-color);
        transition: var(--transition);
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        border: 2px solid transparent;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

    .booking-tab:hover {
        transform: translateY(-2px);
    }

    .booking-tab.active {
        background: var(--primary-gradient);
        color: white;
        border-color: transparent;
    }

    .booking-tab-count {
        display: inline-flex;
            align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 24px;
        border-radius: 12px;
        background: rgba(255,255,255,0.2);
        font-size: 0.85rem;
        padding: 0 0.5rem;
    }

    .booking-tab.active .booking-tab-count {
        background: rgba(0,0,0,0.1);
    }

    .booking-section {
        display: none;
    }

    .booking-section.active {
        display: block;
    }

    .booking-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        overflow: hidden;
        margin-bottom: 2rem;
        display: flex;
        flex-direction: column;
        transition: var(--transition);
        border: 1px solid rgba(0,0,0,0.05);
    }

    .booking-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }

    .booking-card-header {
        background: var(--primary-gradient);
        color: white;
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .booking-card-header h2 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 600;
    }

    .booking-id {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        font-weight: 400;
    }

    .booking-status {
        padding: 0.35rem 0.8rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        background: rgba(255,255,255,0.2);
    }

    .booking-content {
        display: flex;
        flex-wrap: wrap;
    }

    .booking-car-image {
        width: 100%;
        max-width: 300px;
        padding: 1.5rem;
    }

    .booking-car-image img {
            width: 100%;
        height: auto;
        border-radius: var(--border-radius);
            object-fit: cover;
        }

        .booking-details {
        flex: 1;
            padding: 1.5rem;
        min-width: 300px;
        }

        .booking-car-name {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        color: var(--dark-color);
        font-weight: 600;
        }

    .booking-info-grid {
            display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

    .booking-info-item {
        display: flex;
        flex-direction: column;
    }

    .booking-info-label {
        font-size: 0.9rem;
        color: #777;
        margin-bottom: 0.3rem;
    }

    .booking-info-value {
        font-size: 1.1rem;
        color: var(--dark-color);
        font-weight: 500;
    }

    .booking-price {
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--primary-color);
            display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .price-details {
        margin-left: 0.5rem;
        font-size: 0.9rem;
        color: #777;
        font-weight: 400;
    }

    .booking-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .action-btn {
        padding: 0.7rem 1.3rem;
        border-radius: var(--border-radius);
        font-weight: 500;
        display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        text-decoration: none;
        transition: var(--transition);
        font-size: 0.95rem;
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

    .btn-cancel {
        background: transparent;
        color: #dc3545;
        border: 1px solid #dc3545;
    }

    .btn-cancel:hover {
        background: #dc3545;
        color: white;
        transform: translateY(-3px);
    }

    .booking-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #f5f5f5;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #777;
        font-size: 0.9rem;
    }

    .booking-date {
            display: flex;
            align-items: center;
            gap: 0.5rem;
    }

    .no-bookings {
        padding: 3rem;
        text-align: center;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
    }

    .no-bookings i {
        font-size: 3rem;
        color: #ddd;
        margin-bottom: 1rem;
    }

    .no-bookings h3 {
        font-size: 1.5rem;
        color: var(--dark-color);
            margin-bottom: 1rem;
        }

    .no-bookings p {
        color: #777;
        margin-bottom: 1.5rem;
    }

    /* Cancelled bookings styling */
    .booking-section-cancelled .booking-card-header {
        background: #6c757d;
    }

    .booking-section-cancelled .booking-car-name {
        color: #6c757d;
    }

    .booking-section-cancelled .booking-price {
        color: #6c757d;
    }

    /* Modal styling */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.6);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal.show {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: var(--border-radius);
        width: 90%;
        max-width: 500px;
        padding: 2rem;
        position: relative;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .modal-header {
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .modal-header h3 {
            font-size: 1.5rem;
        color: var(--dark-color);
    }

    .modal-body {
        margin-bottom: 1.5rem;
    }

    .modal-footer {
            display: flex;
        justify-content: center;
            gap: 1rem;
    }

    .modal-btn {
        padding: 0.7rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 500;
            border: none;
            cursor: pointer;
        transition: var(--transition);
    }

    .modal-btn-cancel {
        background: #f5f5f5;
        color: var(--dark-color);
    }

    .modal-btn-cancel:hover {
        background: #e0e0e0;
    }

    .modal-btn-confirm {
            background: #dc3545;
        color: white;
        }

    .modal-btn-confirm:hover {
            background: #c82333;
        }

    .close-modal {
        position: absolute;
        top: 1rem;
        right: 1rem;
        font-size: 1.5rem;
        color: #aaa;
        background: none;
        border: none;
        cursor: pointer;
        transition: var(--transition);
    }

    .close-modal:hover {
        color: var(--dark-color);
    }

    /* Alert styles */
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
        .booking-card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .booking-content {
            flex-direction: column;
        }
        
        .booking-car-image {
            max-width: 100%;
        }
        
        .booking-actions {
            flex-direction: column;
        }
        
        .action-btn {
            width: 100%;
            justify-content: center;
        }
        
        .booking-footer {
            flex-direction: column;
            gap: 0.5rem;
            align-items: flex-start;
        }
    }
</style>
EOT;

// Add extra scripts for the bookings page
$extraScripts = <<<EOT
// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.booking-tab');
    const sections = document.querySelectorAll('.booking-section');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const target = this.dataset.target;
            
            // Update active tab
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Show corresponding section
            sections.forEach(section => {
                section.classList.remove('active');
                if (section.id === target) {
                    section.classList.add('active');
                }
            });
        });
    });
    
    // Modal functionality
    const modal = document.getElementById('cancelModal');
    const cancelBtns = document.querySelectorAll('.btn-cancel');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelForm = document.getElementById('cancelForm');
    const bookingIdInput = document.getElementById('modal-booking-id');
    
    cancelBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const bookingId = this.dataset.bookingId;
            const carName = this.dataset.carName;
            
            // Update modal content
            document.getElementById('modal-car-name').textContent = carName;
            bookingIdInput.value = bookingId;
            
            // Show modal
            modal.classList.add('show');
        });
    });
    
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            modal.classList.remove('show');
        });
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('show');
        }
    });
    
    // Submit form on confirm
    const confirmBtn = document.querySelector('.modal-btn-confirm');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            cancelForm.submit();
        });
    }
});
EOT;

// Include header
include 'header.php';
?>

<section class="booking-header">
    <div class="container">
        <h1>My Bookings</h1>
        <p>Manage your car rental bookings and view your reservation history</p>
    </div>
</section>

<div class="booking-container">
    <div class="container">
        <?php if(isset($cancelMessage)) echo $cancelMessage; ?>
        
        <div class="booking-tabs">
            <div class="booking-tab active" data-target="active-bookings">
                <i class="fas fa-calendar-check"></i>
                Active Bookings
                <span class="booking-tab-count"><?php echo $activeBookings->num_rows; ?></span>
                </div>
            <div class="booking-tab" data-target="cancelled-bookings">
                <i class="fas fa-calendar-times"></i>
                Cancelled Bookings
                <span class="booking-tab-count"><?php echo $cancelledBookings->num_rows; ?></span>
            </div>
        </div>
        
        <!-- Active Bookings Section -->
        <div class="booking-section booking-section-active active" id="active-bookings">
            <?php if ($activeBookings->num_rows > 0): ?>
                <?php while ($booking = $activeBookings->fetch_assoc()): ?>
                    <div class="booking-card">
                        <div class="booking-card-header">
                            <div class="booking-id">
                                <i class="fas fa-ticket-alt"></i>
                                Booking ID: #<?php echo $booking['BOOK_ID']; ?>
                            </div>
                            <div class="booking-status">
                                <?php echo $booking['BOOK_STATUS']; ?>
                            </div>
                        </div>
                        <div class="booking-content">
                            <div class="booking-car-image">
                                <img src="https://mdbootstrap.com/img/new/standard/city/043.jpg" alt="<?php echo $booking['CAR_NAME']; ?>">
                            </div>
                            <div class="booking-details">
                                <h3 class="booking-car-name"><?php echo $booking['CAR_NAME']; ?></h3>
                                <div class="booking-info-grid">
                                    <div class="booking-info-item">
                                        <div class="booking-info-label">Start Date</div>
                                        <div class="booking-info-value">
                                            <i class="far fa-calendar-alt"></i> 
                                            <?php echo date('d M Y', strtotime($booking['BOOK_DATE'])); ?>
                                        </div>
                                    </div>
                                    <div class="booking-info-item">
                                        <div class="booking-info-label">End Date</div>
                                        <div class="booking-info-value">
                                            <i class="far fa-calendar-alt"></i> 
                                            <?php echo date('d M Y', strtotime($booking['RETURN_DATE'])); ?>
                                        </div>
                                    </div>
                                    <div class="booking-info-item">
                                        <div class="booking-info-label">Duration</div>
                                        <div class="booking-info-value">
                                            <i class="far fa-clock"></i> 
                                            <?php 
                                                $start = new DateTime($booking['BOOK_DATE']);
                                                $end = new DateTime($booking['RETURN_DATE']);
                                                $duration = $start->diff($end)->days;
                                                echo $duration . ' days';
                                            ?>
                                        </div>
                                    </div>
                                    <div class="booking-info-item">
                                        <div class="booking-info-label">Pickup</div>
                                        <div class="booking-info-value">
                                            <i class="fas fa-map-marker-alt"></i> 
                                            <?php echo $booking['BOOK_PLACE']; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="booking-price">
                                    $<?php echo number_format($booking['PRICE'], 2); ?>
                                    <span class="price-details">
                                        (<?php echo $duration; ?> days x $<?php echo number_format($duration > 0 ? $booking['PRICE']/$duration : $booking['PRICE'], 2); ?>)
                                    </span>
                                </div>
                                <div class="booking-actions">
                                    <a href="cardetails.php?car_id=<?php echo $booking['CAR_ID']; ?>" class="action-btn btn-outline">
                                        <i class="fas fa-info-circle"></i> View Car Details
                                    </a>
            <?php
                                    // Only show cancel button if the start date is in the future
                                    $startDate = new DateTime($booking['BOOK_DATE']);
                                    $today = new DateTime();
                                    if ($startDate > $today):
                                    ?>
                                    <button class="action-btn btn-cancel" 
                                            data-booking-id="<?php echo $booking['BOOK_ID']; ?>"
                                            data-car-name="<?php echo $booking['CAR_NAME']; ?>">
                                        <i class="fas fa-times-circle"></i> Cancel Booking
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="booking-footer">
                            <div class="booking-date">
                                <i class="far fa-calendar-check"></i>
                                Booked on: <?php echo date('d M Y', strtotime($booking['BOOK_DATE'])); ?>
                            </div>
                            <div class="booking-reference">
                                Payment Method: <?php echo isset($booking['PAYMENT_METHOD']) ? $booking['PAYMENT_METHOD'] : 'Cash'; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-bookings">
                    <i class="far fa-calendar-times"></i>
                    <h3>No Active Bookings Found</h3>
                    <p>You don't have any active bookings at the moment. Start exploring our fleet to book your next car rental.</p>
                    <a href="cardetails.php" class="action-btn btn-primary">
                        <i class="fas fa-car"></i> Browse Cars
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Cancelled Bookings Section -->
        <div class="booking-section booking-section-cancelled" id="cancelled-bookings">
            <?php if ($cancelledBookings->num_rows > 0): ?>
                <?php while ($booking = $cancelledBookings->fetch_assoc()): ?>
                    <div class="booking-card">
                        <div class="booking-card-header">
                            <div class="booking-id">
                                <i class="fas fa-ticket-alt"></i>
                                Booking ID: #<?php echo $booking['BOOK_ID']; ?>
                            </div>
                            <div class="booking-status">
                                <?php echo $booking['BOOK_STATUS']; ?>
                            </div>
                        </div>
                        <div class="booking-content">
                            <div class="booking-car-image">
                                <img src="https://mdbootstrap.com/img/new/standard/city/043.jpg" alt="<?php echo $booking['CAR_NAME']; ?>">
                            </div>
                            <div class="booking-details">
                                <h3 class="booking-car-name"><?php echo $booking['CAR_NAME']; ?></h3>
                                <div class="booking-info-grid">
                                    <div class="booking-info-item">
                                        <div class="booking-info-label">Start Date</div>
                                        <div class="booking-info-value">
                                            <i class="far fa-calendar-alt"></i> 
                                            <?php echo date('d M Y', strtotime($booking['BOOK_DATE'])); ?>
                                        </div>
                                    </div>
                                    <div class="booking-info-item">
                                        <div class="booking-info-label">End Date</div>
                                        <div class="booking-info-value">
                                            <i class="far fa-calendar-alt"></i> 
                                            <?php echo date('d M Y', strtotime($booking['RETURN_DATE'])); ?>
                                        </div>
                                    </div>
                                    <div class="booking-info-item">
                                        <div class="booking-info-label">Duration</div>
                                        <div class="booking-info-value">
                                            <i class="far fa-clock"></i> 
                                            <?php 
                                                $start = new DateTime($booking['BOOK_DATE']);
                                                $end = new DateTime($booking['RETURN_DATE']);
                                                $duration = $start->diff($end)->days;
                                                echo $duration . ' days';
                                            ?>
                                        </div>
                                    </div>
                                    <div class="booking-info-item">
                                        <div class="booking-info-label">Pickup</div>
                                        <div class="booking-info-value">
                                            <i class="fas fa-map-marker-alt"></i> 
                                            <?php echo $booking['BOOK_PLACE']; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="booking-price">
                                    $<?php echo number_format($booking['PRICE'], 2); ?>
                                    <span class="price-details">
                                        (<?php echo $duration; ?> days x $<?php echo number_format($duration > 0 ? $booking['PRICE']/$duration : $booking['PRICE'], 2); ?>)
                                    </span>
                                </div>
                                <div class="booking-actions">
                                    <a href="cardetails.php?car_id=<?php echo $booking['CAR_ID']; ?>" class="action-btn btn-primary">
                                        <i class="fas fa-car"></i> Book Again
                                    </a>
                                    <a href="cardetails.php?car_id=<?php echo $booking['CAR_ID']; ?>" class="action-btn btn-outline">
                                        <i class="fas fa-info-circle"></i> View Car Details
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="booking-footer">
                            <div class="booking-date">
                                <i class="far fa-calendar-check"></i>
                                Booked on: <?php echo date('d M Y', strtotime($booking['BOOK_DATE'])); ?>
                            </div>
                            <div class="booking-reference">
                                Cancelled on: <?php echo date('d M Y', strtotime($booking['UPDATED_AT'] ?? $booking['BOOK_DATE'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
            <div class="no-bookings">
                    <i class="far fa-calendar-check"></i>
                    <h3>No Cancelled Bookings</h3>
                    <p>You don't have any cancelled bookings in your history.</p>
                    <a href="cardetails.php" class="action-btn btn-primary">
                        <i class="fas fa-car"></i> Browse Cars
                </a>
            </div>
            <?php endif; ?>
        </div>
        </div>
    </div>

<!-- Cancel Booking Modal -->
<div class="modal" id="cancelModal">
    <div class="modal-content">
        <button class="close-modal">&times;</button>
        <div class="modal-header">
            <h3>Cancel Booking</h3>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to cancel your booking for <strong id="modal-car-name"></strong>?</p>
            <p>This action cannot be undone.</p>
            <form id="cancelForm" method="POST">
                <input type="hidden" name="cancel_booking" value="1">
                <input type="hidden" name="booking_id" id="modal-booking-id">
            </form>
        </div>
        <div class="modal-footer">
            <button class="modal-btn modal-btn-cancel">No, Keep Booking</button>
            <button class="modal-btn modal-btn-confirm">Yes, Cancel Booking</button>
        </div>
    </div>
</div>

<?php
include 'footer.php';
?>