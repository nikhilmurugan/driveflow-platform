<?php
require_once 'connection.php';

// Check if vendor directory exists, if not create a composer.json file
if (!file_exists('vendor/autoload.php')) {
    // Display error message but continue execution
    echo "<div style='color: red; padding: 10px; background-color: #ffe6e6; border-radius: 5px; margin: 10px 0;'>
            <strong>Warning:</strong> SMS notification system is not fully configured. Missing vendor/autoload.php file.
            <br>Please run 'composer require twilio/sdk' in the project directory to enable SMS notifications.
          </div>";
}

class SMSNotification {
    private $conn;
    private $twilioAccountSid;
    private $twilioAuthToken;
    private $twilioPhoneNumber;
    
    public function __construct($conn) {
        $this->conn = $conn;
        
        // These would typically be stored in a secure configuration file
        // For demonstration purposes, they're defined here
        $this->twilioAccountSid = 'YOUR_TWILIO_ACCOUNT_SID';
        $this->twilioAuthToken = 'YOUR_TWILIO_AUTH_TOKEN';
        $this->twilioPhoneNumber = 'YOUR_TWILIO_PHONE_NUMBER';
    }
    
    /**
     * Send booking confirmation SMS
     * 
     * @param int $bookingId The booking ID
     * @return bool True if SMS sent successfully, false otherwise
     */
    public function sendBookingConfirmation($bookingId) {
        try {
            // Get booking details
            $stmt = $this->conn->prepare("SELECT b.*, c.CAR_NAME, u.FNAME, u.LNAME, u.PHONE_NUMBER 
                                         FROM booking b 
                                         JOIN cars c ON b.CAR_ID = c.CAR_ID 
                                         JOIN users u ON b.EMAIL = u.EMAIL 
                                         WHERE b.BOOKING_ID = ?");
            $stmt->bind_param("i", $bookingId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return false;
            }
            
            $booking = $result->fetch_assoc();
            $phoneNumber = $booking['PHONE_NUMBER'];
            $customerName = $booking['FNAME'] . ' ' . $booking['LNAME'];
            $carName = $booking['CAR_NAME'];
            $bookDate = date('d M Y', strtotime($booking['BOOK_DATE']));
            $returnDate = date('d M Y', strtotime($booking['RETURN_DATE']));
            
            // Prepare message
            $message = "Hello $customerName, your booking for $carName from $bookDate to $returnDate has been confirmed. Booking ID: $bookingId. Thank you for choosing CaRs!";
            
            // Check if Twilio is available
            if (file_exists('vendor/autoload.php')) {
                require_once 'vendor/autoload.php';
                
                // Initialize Twilio client
                $twilio = new \Twilio\Rest\Client($this->twilioAccountSid, $this->twilioAuthToken);
                
                // Send SMS
                $twilio->messages->create(
                    $phoneNumber,
                    [
                        'from' => $this->twilioPhoneNumber,
                        'body' => $message
                    ]
                );
                
                // Log the SMS
                $this->logSMS($bookingId, $phoneNumber, $message, 'SENT');
                
                return true;
            } else {
                // Log that SMS couldn't be sent due to missing dependencies
                $this->logSMS($bookingId, $phoneNumber, $message, 'FAILED - Missing Twilio SDK');
                return false;
            }
        } catch (Exception $e) {
            // Log error
            $this->logSMS($bookingId, $phoneNumber ?? 'Unknown', $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Send booking status update SMS
     * 
     * @param int $bookingId The booking ID
     * @param string $status New booking status
     * @return bool True if SMS sent successfully, false otherwise
     */
    public function sendStatusUpdate($bookingId, $status) {
        try {
            // Get booking details
            $stmt = $this->conn->prepare("SELECT b.*, c.CAR_NAME, u.FNAME, u.LNAME, u.PHONE_NUMBER 
                                         FROM booking b 
                                         JOIN cars c ON b.CAR_ID = c.CAR_ID 
                                         JOIN users u ON b.EMAIL = u.EMAIL 
                                         WHERE b.BOOKING_ID = ?");
            $stmt->bind_param("i", $bookingId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return false;
            }
            
            $booking = $result->fetch_assoc();
            $phoneNumber = $booking['PHONE_NUMBER'];
            $customerName = $booking['FNAME'] . ' ' . $booking['LNAME'];
            $carName = $booking['CAR_NAME'];
            
            // Prepare message based on status
            $message = "Hello $customerName, your booking (ID: $bookingId) for $carName has been ";
            
            switch (strtoupper($status)) {
                case 'APPROVED':
                    $message .= "approved. Please proceed with payment to confirm your booking.";
                    break;
                case 'CONFIRMED':
                    $message .= "confirmed. Your car is ready for pickup as scheduled.";
                    break;
                case 'CANCELLED':
                    $message .= "cancelled. If you did not request this cancellation, please contact us.";
                    break;
                case 'COMPLETED':
                    $message .= "marked as completed. Thank you for choosing CaRs!";
                    break;
                default:
                    $message .= "updated to $status.";
            }
            
            // Check if Twilio is available
            if (file_exists('vendor/autoload.php')) {
                require_once 'vendor/autoload.php';
                
                // Initialize Twilio client
                $twilio = new \Twilio\Rest\Client($this->twilioAccountSid, $this->twilioAuthToken);
                
                // Send SMS
                $twilio->messages->create(
                    $phoneNumber,
                    [
                        'from' => $this->twilioPhoneNumber,
                        'body' => $message
                    ]
                );
                
                // Log the SMS
                $this->logSMS($bookingId, $phoneNumber, $message, 'SENT');
                
                return true;
            } else {
                // Log that SMS couldn't be sent due to missing dependencies
                $this->logSMS($bookingId, $phoneNumber, $message, 'FAILED - Missing Twilio SDK');
                return false;
            }
        } catch (Exception $e) {
            // Log error
            $this->logSMS($bookingId, $phoneNumber ?? 'Unknown', $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Log SMS details to database
     * 
     * @param int $bookingId The booking ID
     * @param string $phoneNumber Recipient phone number
     * @param string $message SMS content
     * @param string $status SMS status
     * @return void
     */
    private function logSMS($bookingId, $phoneNumber, $message, $status) {
        try {
            // Check if sms_logs table exists, if not create it
            $tableCheck = $this->conn->query("SHOW TABLES LIKE 'sms_logs'");
            if ($tableCheck->num_rows === 0) {
                $this->conn->query("CREATE TABLE sms_logs (
                    id INT(11) AUTO_INCREMENT PRIMARY KEY,
                    booking_id INT(11) NOT NULL,
                    phone_number VARCHAR(20) NOT NULL,
                    message TEXT NOT NULL,
                    status VARCHAR(50) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
            }
            
            // Log the SMS
            $stmt = $this->conn->prepare("INSERT INTO sms_logs (booking_id, phone_number, message, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $bookingId, $phoneNumber, $message, $status);
            $stmt->execute();
        } catch (Exception $e) {
            // Just suppress errors in logging
        }
    }
}

// Example usage:
// $smsNotification = new SMSNotification($conn);
// $smsNotification->sendBookingConfirmation($bookingId);
// $smsNotification->sendStatusUpdate($bookingId, 'APPROVED');
?>