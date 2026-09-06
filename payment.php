<?php
require_once('connection.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Check if booking_id is provided
if (!isset($_GET['booking_id'])) {
    header("Location: cardetails.php");
    exit();
}

$booking_id = mysqli_real_escape_string($conn, $_GET['booking_id']);
$email = $_SESSION['email'];

// Get booking details with car information
$sql = "SELECT b.*, c.CAR_NAME, c.CAR_IMG, c.PRICE as CAR_PRICE 
        FROM booking b 
        JOIN cars c ON b.CAR_ID = c.CAR_ID 
        WHERE b.BOOK_ID = ? AND b.EMAIL = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $booking_id, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: cardetails.php");
    exit();
}

$booking = $result->fetch_assoc();

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update booking status to CONFIRMED
    $sql = "UPDATE booking SET BOOK_STATUS = 'CONFIRMED' WHERE BOOK_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $booking_id);
    
    if ($stmt->execute()) {
        header("Location: payment_success.php?booking_id=" . $booking_id);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - CaRs</title>
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
            padding-top: 80px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 2rem;
        }

        .booking-summary {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: sticky;
            top: 100px;
        }

        .car-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .summary-header {
            margin-bottom: 1.5rem;
        }

        .summary-header h2 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .booking-details {
            margin-bottom: 1.5rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .total-amount {
            background: #4158D0;
            color: white;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            font-size: 1.25rem;
            font-weight: 600;
            margin-top: 1rem;
        }

        .payment-form {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .form-header {
            margin-bottom: 2rem;
        }

        .form-header h1 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: #333;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #eee;
            border-radius: 5px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #4158D0;
            box-shadow: 0 0 0 3px rgba(65, 88, 208, 0.1);
        }

        .card-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1rem;
        }

        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-primary {
            background: #4158D0;
            color: white;
        }

        .btn-primary:hover {
            background: #3448a5;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .payment-method {
            flex: 1;
            padding: 1rem;
            border: 2px solid #eee;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover,
        .payment-method.active {
            border-color: #4158D0;
            background: rgba(65, 88, 208, 0.05);
        }

        .payment-method i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #4158D0;
        }

        .payment-form-container {
            display: none;
        }

        .payment-form-container.active {
            display: block;
        }

        .bank-details {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .bank-details h3 {
            color: #333;
            margin-bottom: 1rem;
        }

        .bank-info {
            margin-bottom: 1rem;
        }

        .bank-info strong {
            display: block;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .bank-info span {
            color: #666;
        }

        .copy-btn {
            background: none;
            border: none;
            color: #4158D0;
            cursor: pointer;
            padding: 0;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .copy-btn:hover {
            text-decoration: underline;
        }

        #paypal-button-container {
            margin-top: 1.5rem;
        }

        .payment-note {
            margin-top: 1rem;
            padding: 1rem;
            background: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 5px;
            color: #856404;
        }

        .upi-qr-section {
            text-align: center;
            margin: 1.5rem 0;
        }
        
        .qr-code-container {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 1.5rem;
            display: inline-block;
            margin: 0 auto;
        }
        
        .upi-qr-code {
            max-width: 200px;
            height: auto;
        }
        
        .qr-note {
            margin-top: 1rem;
            color: #666;
            font-size: 0.9rem;
        }
        
        .qr-amount {
            font-weight: 600;
            color: #4158D0;
            margin-top: 0.5rem;
            font-size: 1.1rem;
        }

        @media (max-width: 992px) {
            .container {
                grid-template-columns: 1fr;
            }

            .booking-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .payment-methods {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
    <script src="https://www.paypal.com/sdk/js?client-id=YOUR_PAYPAL_CLIENT_ID&currency=INR"></script>
</head>
<body>
    <div class="container">
        <div class="booking-summary">
            <img src="images/<?php echo htmlspecialchars($booking['CAR_IMG']); ?>" alt="<?php echo htmlspecialchars($booking['CAR_NAME']); ?>" class="car-image">
            <div class="summary-header">
                <h2>Booking Summary</h2>
                <p>Booking ID: #<?php echo htmlspecialchars($booking['BOOK_ID']); ?></p>
            </div>
            <div class="booking-details">
                <div class="detail-item">
                    <span>Car</span>
                    <strong><?php echo htmlspecialchars($booking['CAR_NAME']); ?></strong>
                </div>
                <div class="detail-item">
                    <span>Pick-up Location</span>
                    <strong><?php echo htmlspecialchars($booking['BOOK_PLACE']); ?></strong>
                </div>
                <div class="detail-item">
                    <span>Drop-off Location</span>
                    <strong><?php echo htmlspecialchars($booking['DESTINATION']); ?></strong>
                </div>
                <div class="detail-item">
                    <span>Pick-up Date</span>
                    <strong><?php echo date('d M Y', strtotime($booking['BOOK_DATE'])); ?></strong>
                </div>
                <div class="detail-item">
                    <span>Return Date</span>
                    <strong><?php echo date('d M Y', strtotime($booking['RETURN_DATE'])); ?></strong>
                </div>
                <div class="detail-item">
                    <span>Duration</span>
                    <strong><?php echo htmlspecialchars($booking['DURATION']); ?> days</strong>
                </div>
                <div class="detail-item">
                    <span>Daily Rate</span>
                    <strong>₹<?php echo number_format($booking['CAR_PRICE']); ?></strong>
                </div>
            </div>
            <div class="total-amount">
                Total Amount: ₹<?php echo number_format($booking['PRICE']); ?>
            </div>
        </div>

        <div class="payment-form">
            <div class="form-header">
                <h1>Payment Details</h1>
                <p>Complete your booking by providing payment information</p>
            </div>

            <div class="payment-methods">
                <div class="payment-method" data-method="credit-card">
                    <i class="fas fa-credit-card"></i>
                    <div>Credit Card</div>
                </div>
                <div class="payment-method" data-method="paypal">
                    <i class="fab fa-paypal"></i>
                    <div>PayPal</div>
                </div>
                <div class="payment-method" data-method="upi">
                    <i class="fas fa-mobile-alt"></i>
                    <div>UPI</div>
                </div>
                <div class="payment-method" data-method="bank-transfer">
                    <i class="fas fa-university"></i>
                    <div>Bank Transfer</div>
                </div>
            </div>

            <!-- Credit Card Form -->
            <div id="credit-card-form" class="payment-form-container active">
                <form action="" method="POST" id="payment-form">
                    <div class="form-group">
                        <label for="card_name">Name on Card</label>
                        <input type="text" class="form-control" id="card_name" required>
                    </div>

                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input type="text" class="form-control" id="card_number" maxlength="19" placeholder="1234 5678 9012 3456" required>
                    </div>

                    <div class="card-grid">
                        <div class="form-group">
                            <label for="expiry">Expiry Date</label>
                            <input type="text" class="form-control" id="expiry" maxlength="5" placeholder="MM/YY" required>
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="text" class="form-control" id="cvv" maxlength="3" placeholder="123" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Pay ₹<?php echo number_format($booking['PRICE']); ?></button>
                </form>
            </div>

            <!-- PayPal Form -->
            <div id="paypal-form" class="payment-form-container">
                <div class="payment-note">
                    <i class="fas fa-info-circle"></i>
                    <p style="margin-bottom: 0.5rem"><strong>PayPal Payment Instructions:</strong></p>
                    <ol style="margin-left: 1.5rem; margin-bottom: 0">
                        <li>Click the PayPal button below</li>
                        <li>You'll be redirected to PayPal's secure payment page</li>
                        <li>Log in to your PayPal account or pay with your card</li>
                        <li>Review the amount: ₹<?php echo number_format($booking['PRICE']); ?></li>
                        <li>Confirm your payment</li>
                        <li>Wait to be redirected back to complete your booking</li>
                    </ol>
                </div>
                <div id="paypal-button-container"></div>
            </div>

            <!-- UPI Payment Form -->
            <div id="upi-form" class="payment-form-container">
                <div class="bank-details">
                    <h3>UPI Payment Details</h3>
                    <div class="bank-info">
                        <strong>UPI ID</strong>
                        <span>cars@ybl</span>
                        <button class="copy-btn" data-copy="cars@ybl">
                            <i class="far fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
                
                <div class="upi-qr-section">
                    <h4>Scan QR Code to Pay</h4>
                    <div class="qr-code-container">
                        <?php
                        // Create UPI QR code with amount embedded
                        $upiId = "cars@ybl";
                        $payeeName = "CaRs Rental Services";
                        $amount = $booking['PRICE'];
                        $bookingId = $booking['BOOK_ID'];
                        $currency = "INR";
                        
                        // Format the UPI URL with all parameters including amount
                        $upiUrl = "upi://pay?pa=" . urlencode($upiId) . 
                                 "&pn=" . urlencode($payeeName) . 
                                 "&am=" . urlencode($amount) . 
                                 "&cu=" . urlencode($currency) . 
                                 "&tn=" . urlencode("Booking #" . $bookingId);
                        
                        // QR code API URL
                        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($upiUrl);
                        ?>
                        <img src="<?php echo $qrCodeUrl; ?>" alt="UPI QR Code" class="upi-qr-code">
                        <p class="qr-note">Scan this QR code with any UPI app</p>
                        <p class="qr-amount">Amount: ₹<?php echo number_format($booking['PRICE']); ?></p>
                    </div>
                </div>
                
                <div class="payment-note">
                    <i class="fas fa-info-circle"></i>
                    <p style="margin-bottom: 0.5rem"><strong>UPI Payment Instructions:</strong></p>
                    <ol style="margin-left: 1.5rem; margin-bottom: 0.5rem">
                        <li>Open any UPI app (Google Pay, PhonePe, Paytm, BHIM, etc.)</li>
                        <li>Scan the QR code above or use UPI ID: <strong>cars@ybl</strong></li>
                        <li>The amount (₹<?php echo number_format($booking['PRICE']); ?>) will be automatically filled</li>
                        <li>Verify the payment details and complete the transaction</li>
                        <li>Enter your UPI transaction ID below</li>
                    </ol>
                </div>
                
                <form action="" method="POST" style="margin-top: 1.5rem">
                    <div class="form-group">
                        <label for="upi_transaction_id">UPI Transaction ID</label>
                        <input type="text" class="form-control" id="upi_transaction_id" name="upi_transaction_id" placeholder="Enter your UPI transaction ID" required>
                    </div>
                    <input type="hidden" name="payment_method" value="upi">
                    <button type="submit" class="btn btn-primary">Confirm UPI Payment</button>
                </form>
            </div>

            <!-- Bank Transfer Form -->
            <div id="bank-transfer-form" class="payment-form-container">
                <div class="bank-details">
                    <h3>Bank Account Details</h3>
                    <div class="bank-info">
                        <strong>Bank Name</strong>
                        <span>State Bank of India</span>
                        <button class="copy-btn" data-copy="State Bank of India">
                            <i class="far fa-copy"></i> Copy
                        </button>
                    </div>
                    <div class="bank-info">
                        <strong>Account Number</strong>
                        <span>1234567890</span>
                        <button class="copy-btn" data-copy="1234567890">
                            <i class="far fa-copy"></i> Copy
                        </button>
                    </div>
                    <div class="bank-info">
                        <strong>IFSC Code</strong>
                        <span>SBIN0123456</span>
                        <button class="copy-btn" data-copy="SBIN0123456">
                            <i class="far fa-copy"></i> Copy
                        </button>
                    </div>
                    <div class="bank-info">
                        <strong>Account Holder</strong>
                        <span>CaRs Rental Services</span>
                        <button class="copy-btn" data-copy="CaRs Rental Services">
                            <i class="far fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
                <div class="payment-note">
                    <i class="fas fa-info-circle"></i>
                    <p style="margin-bottom: 0.5rem"><strong>Bank Transfer Instructions:</strong></p>
                    <ol style="margin-left: 1.5rem; margin-bottom: 0.5rem">
                        <li>Transfer ₹<?php echo number_format($booking['PRICE']); ?> to the bank account above</li>
                        <li>Use your Booking ID (#<?php echo htmlspecialchars($booking['BOOK_ID']); ?>) as payment reference</li>
                        <li>Take a screenshot/photo of your payment confirmation</li>
                        <li>Send the confirmation to <strong>rentals@cars.com</strong> with:
                            <ul style="margin-left: 1.5rem; margin-top: 0.5rem">
                                <li>Subject: "Payment Confirmation - Booking #<?php echo htmlspecialchars($booking['BOOK_ID']); ?>"</li>
                                <li>Your name and contact number</li>
                                <li>The payment confirmation screenshot/photo</li>
                            </ul>
                        </li>
                    </ol>
                    <p style="margin-top: 0.5rem; color: #004085; background: #cce5ff; padding: 0.5rem; border-radius: 4px;">
                        <i class="fas fa-clock"></i> Your booking will be confirmed within 24 hours after payment verification.
                    </p>
                </div>
                <form action="" method="POST" style="margin-top: 1.5rem">
                    <input type="hidden" name="payment_method" value="bank_transfer">
                    <button type="submit" class="btn btn-primary">I Have Completed the Bank Transfer</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Payment method switching
        const paymentMethods = document.querySelectorAll('.payment-method');
        const paymentForms = document.querySelectorAll('.payment-form-container');

        paymentMethods.forEach(method => {
            method.addEventListener('click', () => {
                // Remove active class from all methods and forms
                paymentMethods.forEach(m => m.classList.remove('active'));
                paymentForms.forEach(f => f.classList.remove('active'));

                // Add active class to clicked method and corresponding form
                method.classList.add('active');
                const formId = `${method.dataset.method}-form`;
                document.getElementById(formId).classList.add('active');
            });
        });

        // Copy button functionality
        document.querySelectorAll('.copy-btn').forEach(button => {
            button.addEventListener('click', () => {
                const textToCopy = button.dataset.copy;
                navigator.clipboard.writeText(textToCopy).then(() => {
                    const originalText = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-check"></i> Copied!';
                    setTimeout(() => {
                        button.innerHTML = originalText;
                    }, 2000);
                });
            });
        });

        // Credit Card Input Formatting
        const cardNumber = document.getElementById('card_number');
        const expiry = document.getElementById('expiry');
        const cvv = document.getElementById('cvv');

        // Format card number with spaces every 4 digits
        cardNumber.addEventListener('input', function(e) {
            let value = e.target.value;
            
            // Remove all non-digits
            value = value.replace(/\D/g, '');
            
            // Add space after every 4 digits
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            
            // Update the input value
            e.target.value = value;
        });

        // Format expiry date as MM/YY
        expiry.addEventListener('input', function(e) {
            let value = e.target.value;
            
            // Remove all non-digits
            value = value.replace(/\D/g, '');
            
            // Add slash after 2 digits for MM/YY format
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2);
            }
            
            // Validate month
            if (value.length >= 2) {
                let month = parseInt(value.substring(0, 2));
                if (month > 12) {
                    value = '12' + value.substring(2);
                }
                if (month < 1) {
                    value = '01' + value.substring(2);
                }
            }
            
            e.target.value = value;
        });

        // Format CVV to only allow 3 digits
        cvv.addEventListener('input', function(e) {
            let value = e.target.value;
            
            // Remove all non-digits
            value = value.replace(/\D/g, '');
            
            // Limit to 3 digits
            value = value.substring(0, 3);
            
            e.target.value = value;
        });

        // Add validation before form submission
        const paymentForm = document.getElementById('payment-form');
        paymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const cardNumberValue = cardNumber.value.replace(/\s/g, '');
            const expiryValue = expiry.value;
            const cvvValue = cvv.value;
            
            // Validate card number (16 digits)
            if (!/^\d{16}$/.test(cardNumberValue)) {
                alert('Please enter a valid 16-digit card number');
                return;
            }
            
            // Validate expiry date (MM/YY format)
            if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiryValue)) {
                alert('Please enter a valid expiry date (MM/YY)');
                return;
            }
            
            // Validate CVV (3 digits)
            if (!/^\d{3}$/.test(cvvValue)) {
                alert('Please enter a valid 3-digit CVV');
                return;
            }
            
            // If all validations pass, submit the form
            this.submit();
        });

        // PayPal integration
        paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '<?php echo $booking['PRICE']; ?>'
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    // Submit the form to update booking status
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '';
                    document.body.appendChild(form);
                    form.submit();
                });
            }
        }).render('#paypal-button-container');
    </script>
</body>
</html>