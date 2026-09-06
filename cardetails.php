<?php
require 'connection.php';
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT FNAME FROM users WHERE EMAIL = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userName = $user ? $user['FNAME'] : 'Account';

// Check if we're viewing a specific car (from the "View Car Details" link)
$viewingSingleCar = false;
$carDetails = null;

// Process car_id or id parameter (supporting both formats)
if (isset($_GET['car_id']) || isset($_GET['id'])) {
    $carId = isset($_GET['car_id']) ? $_GET['car_id'] : $_GET['id'];
    $carId = (int)$carId; // Convert to integer for security
    
    // Fetch the specific car details
    $carQuery = "SELECT * FROM cars WHERE CAR_ID = ?";
    $stmt = $conn->prepare($carQuery);
    $stmt->bind_param("i", $carId);
    $stmt->execute();
    $carResult = $stmt->get_result();
    
    if ($carResult->num_rows > 0) {
        $viewingSingleCar = true;
        $carDetails = $carResult->fetch_assoc();
        
        // Set page title based on car name
        $pageTitle = $carDetails['CAR_NAME'] . " - CaRs";
    } else {
        // Car not found, redirect to main car listing
        header("Location: cardetails.php");
        exit();
    }
} else {
    // Regular car listing page title
    $pageTitle = "Available Cars - CaRs";
}

// Cookie settings
$cookie_duration = time() + (30 * 24 * 60 * 60); // 30 days
$cookie_path = '/';

// Handle cookie consent
if (isset($_POST['cookie_consent'])) {
    setcookie('cookie_consent', 'accepted', $cookie_duration, $cookie_path);
} elseif (isset($_POST['cookie_decline'])) {
    // Clear all existing cookies
    if (isset($_SERVER['HTTP_COOKIE'])) {
        $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
        foreach($cookies as $cookie) {
            $parts = explode('=', $cookie);
            $name = trim($parts[0]);
            setcookie($name, '', time() - 3600, $cookie_path);
        }
    }
}

// Get filter values from GET parameters or cookies
$fuelType = isset($_GET['fuel_type']) ? $_GET['fuel_type'] : 
           (isset($_COOKIE['preferred_fuel_type']) ? $_COOKIE['preferred_fuel_type'] : '');
$capacity = isset($_GET['capacity']) ? (int)$_GET['capacity'] : 
           (isset($_COOKIE['preferred_capacity']) ? (int)$_COOKIE['preferred_capacity'] : 0);
$minPrice = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 
           (isset($_COOKIE['preferred_min_price']) ? (int)$_COOKIE['preferred_min_price'] : 0);
$maxPrice = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 
           (isset($_COOKIE['preferred_max_price']) ? (int)$_COOKIE['preferred_max_price'] : 100000);

// Initialize date variables with defaults and cookie values
$today = date('Y-m-d');
$defaultEndDate = date('Y-m-d', strtotime('+3 days'));

$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : 
            (isset($_COOKIE['preferred_start_date']) ? $_COOKIE['preferred_start_date'] : $today);
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : 
          (isset($_COOKIE['preferred_end_date']) ? $_COOKIE['preferred_end_date'] : $defaultEndDate);

// Validate dates
if (strtotime($startDate) < strtotime($today)) {
    $startDate = $today;
}
if (strtotime($endDate) < strtotime($startDate)) {
    $endDate = date('Y-m-d', strtotime($startDate . ' +3 days'));
}

// Save filter preferences in cookies when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['fuel_type']) || isset($_GET['capacity']) || isset($_GET['min_price']))) {
    if (isset($_COOKIE['cookie_consent']) && $_COOKIE['cookie_consent'] === 'accepted') {
        setcookie('preferred_fuel_type', $fuelType, $cookie_duration, $cookie_path);
        setcookie('preferred_capacity', $capacity, $cookie_duration, $cookie_path);
        setcookie('preferred_min_price', $minPrice, $cookie_duration, $cookie_path);
        setcookie('preferred_max_price', $maxPrice, $cookie_duration, $cookie_path);
        setcookie('preferred_start_date', $startDate, $cookie_duration, $cookie_path);
        setcookie('preferred_end_date', $endDate, $cookie_duration, $cookie_path);
    }
}

// Build the SQL query with filters
$sql = "SELECT * FROM cars WHERE AVAILABLE = 'YES'";

// Add fuel type filter if selected
if (!empty($fuelType)) {
    $sql .= " AND FUEL_TYPE = '" . mysqli_real_escape_string($conn, $fuelType) . "'";
}

// Add capacity filter if selected
if ($capacity > 0) {
    $sql .= " AND CAPACITY = " . $capacity;
}

// Add price range filter
$sql .= " AND PRICE BETWEEN " . $minPrice . " AND " . $maxPrice;

// Exclude cars that are booked for the selected dates
if (!empty($startDate) && !empty($endDate)) {
    $sql .= " AND CAR_ID NOT IN (
        SELECT DISTINCT b.CAR_ID FROM booking b 
        WHERE b.BOOK_STATUS IN ('PENDING', 'APPROVED', 'CONFIRMED') 
        AND (
            (b.BOOK_DATE <= '" . mysqli_real_escape_string($conn, $endDate) . "' AND b.RETURN_DATE >= '" . mysqli_real_escape_string($conn, $startDate) . "') OR 
            (b.BOOK_DATE <= '" . mysqli_real_escape_string($conn, $startDate) . "' AND b.RETURN_DATE >= '" . mysqli_real_escape_string($conn, $startDate) . "') OR
            (b.BOOK_DATE >= '" . mysqli_real_escape_string($conn, $startDate) . "' AND b.RETURN_DATE <= '" . mysqli_real_escape_string($conn, $endDate) . "')
        )
    )";
}

// Add sorting
$sql .= " ORDER BY CAR_NAME";

$result = $conn->query($sql);

// Get distinct fuel types for filter dropdown
$fuelTypesQuery = "SELECT DISTINCT FUEL_TYPE FROM cars ORDER BY FUEL_TYPE";
$fuelTypesResult = $conn->query($fuelTypesQuery);

// Get distinct capacities for filter dropdown
$capacitiesQuery = "SELECT DISTINCT CAPACITY FROM cars ORDER BY CAPACITY";
$capacitiesResult = $conn->query($capacitiesQuery);

// Get min and max prices for the price slider
$priceRangeQuery = "SELECT MIN(PRICE) as min_price, MAX(PRICE) as max_price FROM cars";
$priceRangeResult = $conn->query($priceRangeQuery);
$priceRange = $priceRangeResult->fetch_assoc();
$dbMinPrice = $priceRange['min_price'];
$dbMaxPrice = $priceRange['max_price'];

// Set default price range if not specified
if ($minPrice == 0) {
    $minPrice = $dbMinPrice;
}
if ($maxPrice == 100000) {
    $maxPrice = $dbMaxPrice;
}

// Enhanced Car Recommendation System
function getRecommendedCars($conn, $filterParams, $userEmail) {
    // Extract filter parameters
    $fuelType = $filterParams['fuelType'] ?? '';
    $capacity = $filterParams['capacity'] ?? 0;
    $minPrice = $filterParams['minPrice'] ?? 0;
    $maxPrice = $filterParams['maxPrice'] ?? 100000;
    
    // Get user's rental history and preferences
    $userPreferences = getUserPreferences($conn, $userEmail);
    
    // Build recommendation query with weighted scoring
    $sql = "
        SELECT 
            c.*,
            (
                CASE
                    -- Match preferred fuel type (weight: 3)
                    WHEN c.FUEL_TYPE = ? THEN 3
                    ELSE 0
                END +
                -- Match preferred capacity (weight: 2)
                CASE
                    WHEN c.CAPACITY = ? THEN 2
                    ELSE 0
                END +
                -- Match price range preference (weight: 2)
                CASE
                    WHEN c.PRICE BETWEEN ? AND ? THEN 2
                    ELSE 0
                END +
                -- Previously rented by user (weight: 4)
                CASE
                    WHEN c.CAR_ID IN (
                        SELECT DISTINCT CAR_ID 
                        FROM booking 
                        WHERE EMAIL = ? AND BOOK_STATUS = 'COMPLETED'
                    ) THEN 4
                    ELSE 0
                END +
                -- High rated cars (weight: 2)
                CASE
                    WHEN c.CAR_ID IN (
                        SELECT CAR_ID 
                        FROM feedback 
                        GROUP BY CAR_ID 
                        HAVING AVG(RATING) >= 4
                    ) THEN 2
                    ELSE 0
                END +
                -- Popular in user's price range (weight: 1)
                CASE
                    WHEN c.PRICE BETWEEN ? * 0.8 AND ? * 1.2
                    AND c.CAR_ID IN (
                        SELECT CAR_ID 
                        FROM booking 
                        GROUP BY CAR_ID 
                        HAVING COUNT(*) > 5
                    ) THEN 1
                    ELSE 0
                END
            ) as recommendation_score
        FROM cars c
        WHERE c.AVAILABLE = 'YES'
        -- Basic availability check
        AND c.CAR_ID NOT IN (
            SELECT DISTINCT b.CAR_ID 
            FROM booking b 
            WHERE b.BOOK_STATUS IN ('PENDING', 'APPROVED', 'CONFIRMED')
            AND (
                (b.BOOK_DATE <= ? AND b.RETURN_DATE >= ?) OR
                (b.BOOK_DATE >= ? AND b.RETURN_DATE <= ?)
            )
        )
        ORDER BY recommendation_score DESC, RAND()
        LIMIT 4";

    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "siidsddssss",
        $userPreferences['preferred_fuel_type'],
        $userPreferences['preferred_capacity'],
        $userPreferences['min_price'],
        $userPreferences['max_price'],
        $userEmail,
        $userPreferences['avg_price'],
        $userPreferences['avg_price'],
        $endDate,
        $startDate,
        $startDate,
        $endDate
    );
    $stmt->execute();
    return $stmt->get_result();
}

// Function to get user preferences from history
function getUserPreferences($conn, $userEmail) {
    $preferences = [
        'preferred_fuel_type' => null,
        'preferred_capacity' => 0,
        'min_price' => 0,
        'max_price' => 100000,
        'avg_price' => 0
    ];

    // Get most frequently booked fuel type
    $sql = "
        SELECT c.FUEL_TYPE, COUNT(*) as frequency
        FROM booking b
        JOIN cars c ON b.CAR_ID = c.CAR_ID
        WHERE b.EMAIL = ? AND b.BOOK_STATUS = 'COMPLETED'
        GROUP BY c.FUEL_TYPE
        ORDER BY frequency DESC
        LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $preferences['preferred_fuel_type'] = $row['FUEL_TYPE'];
    }

    // Get most frequently booked capacity
    $sql = "
        SELECT c.CAPACITY, COUNT(*) as frequency
        FROM booking b
        JOIN cars c ON b.CAR_ID = c.CAR_ID
        WHERE b.EMAIL = ? AND b.BOOK_STATUS = 'COMPLETED'
        GROUP BY c.CAPACITY
        ORDER BY frequency DESC
        LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $preferences['preferred_capacity'] = $row['CAPACITY'];
    }

    // Get price range preferences
    $sql = "
        SELECT 
            MIN(c.PRICE) as min_price,
            MAX(c.PRICE) as max_price,
            AVG(c.PRICE) as avg_price
        FROM booking b
        JOIN cars c ON b.CAR_ID = c.CAR_ID
        WHERE b.EMAIL = ? AND b.BOOK_STATUS = 'COMPLETED'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $preferences['min_price'] = $row['min_price'] ?: 0;
        $preferences['max_price'] = $row['max_price'] ?: 100000;
        $preferences['avg_price'] = $row['avg_price'] ?: 5000;
    }

    return $preferences;
}

// Get recommended cars based on user preferences and current filters
$recommendedCars = getRecommendedCars($conn, [
    'fuelType' => $fuelType,
    'capacity' => $capacity,
    'minPrice' => $minPrice,
    'maxPrice' => $maxPrice
], $email);

// Dynamic Pricing System
function calculateDynamicPrice($basePrice, $carId, $startDate, $endDate, $conn) {
    // Data collection and analysis for dynamic pricing
    
    // 1. Demand Factor: Check booking frequency for this car
    $demandQuery = "SELECT COUNT(*) as booking_count FROM booking 
                    WHERE CAR_ID = $carId AND BOOK_STATUS IN ('APPROVED', 'CONFIRMED') 
                    AND BOOK_DATE >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $demandResult = $conn->query($demandQuery);
    $demandData = $demandResult->fetch_assoc();
    $bookingCount = $demandData['booking_count'];
    
    // Calculate demand factor (more bookings = higher price)
    $demandFactor = 1.0;
    if ($bookingCount > 10) {
        $demandFactor = 1.25; // High demand: 25% premium
    } elseif ($bookingCount > 5) {
        $demandFactor = 1.15; // Medium demand: 15% premium
    } elseif ($bookingCount < 2) {
        $demandFactor = 0.9; // Low demand: 10% discount
    }
    
    // 2. Time Factor: Days until rental start
    $currentDate = new DateTime();
    $rentalStart = new DateTime($startDate);
    $daysUntilRental = $currentDate->diff($rentalStart)->days;
    
    $timeFactor = 1.0;
    if ($daysUntilRental < 2) {
        $timeFactor = 1.2; // Last minute booking: 20% premium
    } elseif ($daysUntilRental > 14) {
        $timeFactor = 0.9; // Early booking: 10% discount
    }
    
    // 3. Seasonal Adjustment: Check for weekends and holidays
    $rentalStartObj = new DateTime($startDate);
    $weekDay = $rentalStartObj->format('N'); // 1 (Monday) to 7 (Sunday)
    
    $seasonalFactor = 1.0;
    if ($weekDay >= 6) { // Weekend (Saturday or Sunday)
        $seasonalFactor = 1.15; // Weekend premium: 15%
    }
    
    // Check if it's a holiday season (simplified for demo - actual implementation would check against a holiday database)
    $month = $rentalStartObj->format('m');
    if ($month == '12' || $month == '07') { // December (Christmas) or July (Summer vacation)
        $seasonalFactor *= 1.2; // Holiday season: additional 20% premium
    }
    
    // 4. Duration Factor: Longer rentals get discounts
    $rentalEndObj = new DateTime($endDate);
    $rentalDuration = $rentalStartObj->diff($rentalEndObj)->days + 1;
    
    $durationFactor = 1.0;
    if ($rentalDuration >= 7) {
        $durationFactor = 0.85; // Weekly rental: 15% discount
    } elseif ($rentalDuration >= 3) {
        $durationFactor = 0.95; // 3+ days rental: 5% discount
    }
    
    // Calculate final price with all factors
    $adjustedPrice = $basePrice * $demandFactor * $timeFactor * $seasonalFactor * $durationFactor;
    
    // Round to nearest multiple of 10 for cleaner pricing
    $adjustedPrice = round($adjustedPrice / 10) * 10;
    
    // Prevent extreme price variations (not more than 50% up or 30% down)
    $minPrice = $basePrice * 0.7;
    $maxPrice = $basePrice * 1.5;
    $adjustedPrice = max(min($adjustedPrice, $maxPrice), $minPrice);
    
    return [
        'adjusted_price' => $adjustedPrice,
        'original_price' => $basePrice,
        'demand_factor' => $demandFactor,
        'time_factor' => $timeFactor,
        'seasonal_factor' => $seasonalFactor,
        'duration_factor' => $durationFactor
    ];
}

// Gemini API Integration
function getGeminiResponse($userQuery) {
    $apiKey = 'AIzaSyCsnA22Z0wRbNdHDC4PY_08MCyz8JTUXrE';
    
    // For demonstration purposes, since the API key is having authentication issues,
    // we'll generate helpful responses without calling the API
    $commonQuestions = [
        'pricing' => "Our dynamic pricing system adjusts rates based on several factors:\n\n1. Demand: Popular cars cost more during high demand periods\n2. Timing: Book early to get up to 10% discount; last-minute bookings may cost 20% more\n3. Season: Weekend bookings are 15% higher; holiday seasons have an additional 20% premium\n4. Duration: Rentals of 7+ days get a 15% discount; 3+ days get a 5% discount\n\nYou can always see the exact price breakdown on each car listing.",
        
        'dynamic' => "Our dynamic pricing system adjusts rates based on several factors:\n\n1. Demand: Popular cars cost more during high demand periods\n2. Timing: Book early to get up to 10% discount; last-minute bookings may cost 20% more\n3. Season: Weekend bookings are 15% higher; holiday seasons have an additional 20% premium\n4. Duration: Rentals of 7+ days get a 15% discount; 3+ days get a 5% discount\n\nThis allows us to offer competitive prices while managing availability effectively.",
        
        'booking' => "To book a car, simply follow these steps:\n\n1. Browse available cars and apply filters to find the perfect vehicle\n2. Select your desired booking dates\n3. Click 'Book Now' on the car you'd like to reserve\n4. Complete the payment process\n5. Wait for admin approval of your booking\n\nOnce approved, you'll receive a confirmation email with all the details.",
        
        'reserve' => "To reserve a car:\n\n1. Select your dates using the date picker\n2. Apply any filters (fuel type, capacity, price range)\n3. Browse available cars and click 'Book Now' on your preferred vehicle\n4. Complete your personal details and payment information\n5. Submit your reservation request\n\nAfter admin approval, you'll receive a confirmation email with pickup instructions.",
        
        'payment' => "We accept multiple payment methods:\n\n• Credit/Debit Cards (Visa, Mastercard, American Express)\n• PayPal\n• UPI (with QR code scanning)\n• Bank Transfer\n\nFull payment is required at the time of booking, and a security deposit will be refunded when you return the car.",
        
        'pay' => "We accept multiple payment methods:\n\n• Credit/Debit Cards (Visa, Mastercard, American Express)\n• PayPal\n• UPI (with QR code scanning)\n• Bank Transfer\n\nFull payment is required at the time of booking, and a security deposit will be refunded when you return the car.",
        
        'cancel' => "Our cancellation policy is as follows:\n\n• FREE cancellation up to 24 hours before pickup\n• Cancellations less than 24 hours before pickup incur a 50% fee\n• No-shows will be charged the full amount\n\nTo cancel, simply go to 'My Bookings' and select the booking you wish to cancel.",
        
        'cancellation' => "Our cancellation policy is as follows:\n\n• FREE cancellation up to 24 hours before pickup\n• Cancellations less than 24 hours before pickup incur a 50% fee\n• No-shows will be charged the full amount\n\nTo cancel, simply go to 'My Bookings' and select the booking you wish to cancel.",
        
        'refund' => "Our refund policy works as follows:\n\n• Cancellations more than 24 hours before pickup: Full refund\n• Cancellations less than 24 hours before pickup: 50% refund\n• No-shows: No refund\n\nRefunds are processed to the original payment method and typically appear within 3-5 business days.",
        
        'cars' => "Our fleet includes a variety of cars with different fuel types and seating capacities:\n\n• Fuel types: Petrol, Diesel, CNG, and Electric\n• Seating capacity: 4 to 9 seats\n• All cars include comprehensive insurance and 24/7 roadside assistance\n\nYou can filter cars by fuel type, capacity, price range, and availability dates.",
        
        'electric' => "Yes, we offer electric vehicles in our fleet! Our electric cars come with:\n\n• Full charge at pickup\n• Charging cable included\n• List of nearby charging stations\n• Lower daily rates compared to equivalent petrol models\n\nElectric vehicles are perfect for city driving and short trips. For longer journeys, we recommend checking the range and planning charging stops accordingly.",
        
        'insurance' => "All our cars come with comprehensive insurance included in the price. The insurance covers:\n\n• Third-party liability\n• Collision damage (with standard deductible)\n• Theft protection\n\nFor additional peace of mind, you can purchase our Premium Insurance package which reduces the deductible to zero and includes personal accident insurance for all passengers.",
        
        'deposit' => "We require a security deposit for all rentals, which is fully refundable upon return of the car in the same condition it was rented. The deposit amount varies by car type:\n\n• Economy cars: ₹5,000\n• Mid-range cars: ₹10,000\n• Premium cars: ₹15,000-₹25,000\n\nThe deposit is blocked on your credit card at pickup and released within 3-5 business days after return.",
        
        'contact' => "You can reach our customer service team through:\n\n• Email: support@cars.com\n• Phone: +91-1234567890\n• Live chat: Available here during business hours (9AM-5PM on weekdays)\n\nWe typically respond to all inquiries within 2-4 hours during business hours.",
        
        'address' => "Our main office is located at:\n\nCaRs Headquarters\n123 Automotive Avenue\nMumbai, Maharashtra 400001\nIndia\n\nWe also have pickup/drop-off locations in Delhi, Bangalore, Chennai, and Hyderabad. You can select your preferred location during the booking process.",
        
        'age' => "To rent a car from us, you must be:\n\n• At least 21 years old\n• Have a valid driving license that has been held for at least 1 year\n\nDrivers under 25 may be subject to a young driver surcharge and may have restrictions on certain premium car models.",
        
        'license' => "To rent a car, you'll need:\n\n• A valid driving license (Indian or International)\n• The license must have been held for at least 1 year\n• Photo ID (passport or Aadhar card)\n• Credit card in the driver's name for the security deposit\n\nThese documents will be verified at the time of pickup.",
        
        'fuel' => "Our fuel policy is 'same-to-same', which means:\n\n• You'll receive the car with a full tank of fuel\n• You should return it with a full tank\n• If returned with less fuel, you'll be charged for the missing fuel plus a service fee\n\nWe recommend refueling at stations close to the drop-off location to make this process easier.",
        
        'mileage' => "All our rental packages include unlimited kilometers/mileage at no extra cost. Drive as much as you want without worrying about additional charges!\n\nThis makes our service ideal for long road trips and extended journeys. Just ensure you return the car on the agreed date and time to avoid late fees.",
        
        'hello' => "Hello! I'm your CaRs assistant. I can help you with:\n\n• Finding and booking the right car\n• Understanding our dynamic pricing\n• Payment and cancellation policies\n• General queries about our service\n\nHow can I assist you today?",
        
        'hi' => "Hi there! Welcome to CaRs. I can help you with:\n\n• Finding available cars for your dates\n• Understanding our policies and procedures\n• Answering questions about our rental process\n• Providing information about our fleet\n\nWhat would you like to know about today?",
        
        'thanks' => "You're welcome! I'm glad I could help. Is there anything else you'd like to know about our car rental service?",
        
        'thank you' => "You're welcome! I'm happy I could assist you. If you have any other questions about our car rental service, feel free to ask anytime!",
        
        'pickup' => "Car pickup process:\n\n1. Arrive at your selected location with your booking confirmation, ID, and driver's license\n2. Our staff will verify your documents and process the security deposit\n3. We'll inspect the car with you and note any existing damage\n4. You'll receive a quick overview of the car's features\n5. Sign the rental agreement and you're ready to go!\n\nThe entire process typically takes 15-20 minutes.",
        
        'return' => "Car return process:\n\n1. Return to the same location unless you've arranged for a different drop-off point\n2. Our staff will inspect the car with you present\n3. The fuel level will be checked (should be the same as at pickup)\n4. Return all keys and accessories provided\n5. Sign the return form to complete the process\n\nYour security deposit will be released within 3-5 business days."
    ];
    
    // Clean up the user query for better matching
    $userQueryLower = strtolower(trim($userQuery));
    
    // Check for exact match first
    if (isset($commonQuestions[$userQueryLower])) {
        return simpleGeminiResponse($commonQuestions[$userQueryLower]);
    }
    
    // Simple keyword matching for common questions
    foreach ($commonQuestions as $keyword => $response) {
        if (stripos($userQueryLower, $keyword) !== false) {
            return simpleGeminiResponse($response);
        }
    }
    
    // Default response if no keywords match
    return simpleGeminiResponse("Thanks for your question about \"$userQuery\". While I'm currently using a local response system, I can help with basic information about our car rental service. Please try asking about our pricing, booking process, payment methods, cancellation policy, or car features for more specific information. For other inquiries, please contact our support team at support@cars.com or call +91-1234567890.");
}

// Create a response in the expected Gemini format
function simpleGeminiResponse($text) {
    return [
        'candidates' => [
            [
                'content' => [
                'parts' => [
                        ['text' => $text]
                    ]
                ]
            ]
        ]
    ];
}

// Handle chat API request
if (isset($_POST['user_query'])) {
    $userQuery = $_POST['user_query'];
    $response = getGeminiResponse($userQuery);
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Set page title and include header
$pageTitle = "Available Cars - CaRs";
include 'header.php';

// Add CSS for single car view 
if ($viewingSingleCar): 
?>
<style>
    .single-car-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }
    .car-details-card {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .car-header {
        padding: 2rem;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        color: white;
        position: relative;
    }
    .car-title {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        font-weight: 700;
    }
    .car-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
    }
    .car-price {
        position: absolute;
        top: 2rem;
        right: 2rem;
        font-size: 2rem;
        font-weight: 700;
    }
    .car-price-label {
        font-size: 0.9rem;
        font-weight: 400;
    }
    .car-content {
        display: flex;
        flex-wrap: wrap;
        padding: 2rem;
    }
    .car-image-container {
        flex: 1;
        min-width: 300px;
        padding-right: 2rem;
    }
    .car-image {
        width: 100%;
        height: auto;
        border-radius: 10px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    .car-details {
        flex: 1;
        min-width: 300px;
    }
    .car-specs {
        margin-bottom: 2rem;
    }
    .car-specs h3 {
        font-size: 1.5rem;
        margin-bottom: 1rem;
        color: var(--dark-color);
        position: relative;
        padding-bottom: 0.5rem;
    }
    .car-specs h3:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background: var(--primary-gradient);
    }
    .specs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.5rem;
    }
    .spec-item {
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }
    .spec-icon {
        width: 40px;
        height: 40px;
        background: rgba(65, 88, 208, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
    }
    .spec-label {
        font-size: 0.9rem;
        color: #777;
    }
    .spec-value {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--dark-color);
    }
    .booking-section {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 10px;
        margin-bottom: 1.5rem;
    }
    .booking-section h3 {
        margin-bottom: 1rem;
        color: var(--dark-color);
    }
    .booking-dates {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .date-input {
        flex: 1;
        min-width: 200px;
    }
    .date-input label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    .date-input input {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    .cta-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }
    .btn-book {
        padding: 1rem 2rem;
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        text-align: center;
    }
    .btn-book:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    .btn-back {
        padding: 1rem 2rem;
        background: transparent;
        color: var(--dark-color);
        border: 1px solid #ddd;
        border-radius: 5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        text-align: center;
    }
    .btn-back:hover {
        background: #f5f5f5;
    }
    @media (max-width: 768px) {
        .car-content {
            flex-direction: column;
        }
        .car-image-container {
            padding-right: 0;
            padding-bottom: 2rem;
        }
        .car-price {
            position: static;
            margin-top: 1rem;
        }
        .cta-buttons {
            flex-direction: column;
        }
    }
</style>
<?php endif; ?>

<?php if ($viewingSingleCar): ?>
<!-- Single Car Details View -->
<div class="single-car-container">
    <div class="car-details-card">
        <div class="car-header">
            <h1 class="car-title"><?php echo htmlspecialchars($carDetails['CAR_NAME']); ?></h1>
            <p class="car-subtitle"><?php echo htmlspecialchars($carDetails['FUEL_TYPE']); ?> | <?php echo htmlspecialchars($carDetails['CAPACITY']); ?> Seater</p>
            <div class="car-price">
                ₹<?php echo number_format($carDetails['PRICE']); ?><span class="car-price-label">/day</span>
            </div>
        </div>
        
        <div class="car-content">
            <div class="car-image-container">
                <?php 
                $imagePath = 'images/' . $carDetails['CAR_IMG'];
                $imageSrc = file_exists($imagePath) && !empty($carDetails['CAR_IMG']) ? $imagePath : 'images/carbg.jpg';
                ?>
                <img src="<?php echo $imageSrc; ?>" alt="<?php echo htmlspecialchars($carDetails['CAR_NAME']); ?>" class="car-image">
            </div>
            
            <div class="car-details">
                <div class="car-specs">
                    <h3>Car Specifications</h3>
                    <div class="specs-grid">
                        <div class="spec-item">
                            <div class="spec-icon">
                                <i class="fas fa-gas-pump"></i>
                            </div>
                            <div class="spec-details">
                                <div class="spec-label">Fuel Type</div>
                                <div class="spec-value"><?php echo htmlspecialchars($carDetails['FUEL_TYPE']); ?></div>
                            </div>
                        </div>
                        
                        <div class="spec-item">
                            <div class="spec-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="spec-details">
                                <div class="spec-label">Capacity</div>
                                <div class="spec-value"><?php echo htmlspecialchars($carDetails['CAPACITY']); ?> Seater</div>
                            </div>
                        </div>
                        
                        <div class="spec-item">
                            <div class="spec-icon">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div class="spec-details">
                                <div class="spec-label">Daily Rate</div>
                                <div class="spec-value">₹<?php echo number_format($carDetails['PRICE']); ?></div>
                            </div>
                        </div>
                        
                        <div class="spec-item">
                            <div class="spec-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="spec-details">
                                <div class="spec-label">Status</div>
                                <div class="spec-value"><?php echo ($carDetails['AVAILABLE'] == 'Y' || $carDetails['AVAILABLE'] == 'YES') ? 'Available' : 'Not Available'; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($carDetails['AVAILABLE'] == 'Y' || $carDetails['AVAILABLE'] == 'YES'): ?>
                <div class="booking-section">
                    <h3>Book This Car</h3>
                    <form action="booking.php" method="GET">
                        <input type="hidden" name="id" value="<?php echo $carDetails['CAR_ID']; ?>">
                        
                        <div class="booking-dates">
                            <div class="date-input">
                                <label for="start_date">Pick-up Date</label>
                                <input type="date" id="start_date" name="start_date" min="<?php echo date('Y-m-d'); ?>" 
                                       value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="date-input">
                                <label for="end_date">Return Date</label>
                                <input type="date" id="end_date" name="end_date" 
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" 
                                       value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d', strtotime('+3 days')); ?>" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-book">Book Now</button>
                    </form>
                </div>
                <?php else: ?>
                <div class="booking-section">
                    <h3>Car Not Available</h3>
                    <p>Sorry, this car is currently not available for booking. Please check back later or browse our other available cars.</p>
                </div>
                <?php endif; ?>
                
                <div class="cta-buttons">
                    <a href="cardetails.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Back to All Cars
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Recommended for You Section (Moved to top) -->
<div class="recommended-section container">
    <h2>Recommended for You</h2>
    <p class="recommendation-subtitle">Based on your previous rentals and preferences</p>
    
    <?php
    // Define the filter parameters array to fix the undefined variable error
    $filterParams = [
        'fuelType' => $fuelType,
        'capacity' => $capacity,
        'minPrice' => $minPrice,
        'maxPrice' => $maxPrice,
        'startDate' => $startDate,
        'endDate' => $endDate
    ];
    
    // Define userEmail from the existing session email
    $userEmail = isset($_SESSION['email']) ? $_SESSION['email'] : '';
    
    // Get recommended cars using your existing function
    $recommendedCars = getRecommendedCars($conn, $filterParams, $userEmail);
    
    if ($recommendedCars && $recommendedCars->num_rows > 0) {
        echo '<div class="recommended-cars">';
        
        while($car = $recommendedCars->fetch_assoc()) {
            $carImage = !empty($car['CAR_IMG']) ? 'images/' . $car['CAR_IMG'] : 'images/default-car.jpg';
            $rating = rand(35, 50) / 10; // Simulating a rating between 3.5-5.0
            
            echo '<div class="car-card">
                <div class="recommendation-badge">Recommended</div>
                <div class="car-image-container">
                    <img src="' . $carImage . '" alt="' . $car['CAR_NAME'] . '" class="car-image">
                </div>
                <div class="car-details">
                    <h3 class="car-name">' . $car['CAR_NAME'] . '</h3>
                    <div class="car-meta">
                        <div class="car-spec">
                            <i class="fas fa-gas-pump"></i> ' . $car['FUEL_TYPE'] . '
                        </div>
                        <div class="car-spec">
                            <i class="fas fa-users"></i> ' . $car['CAPACITY'] . ' Seats
                        </div>
                        <div class="car-spec">
                            <i class="fas fa-star"></i> ' . $rating . '
                        </div>
                    </div>
                    <div class="car-price">
                        ₹' . $car['PRICE'] . '<span class="price-per-day">/day</span>
                    </div>
                    <a href="booking.php?id=' . $car['CAR_ID'] . '" class="book-now-btn">
                        Book Now <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>';
        }
        
        echo '</div>';
    } else {
        echo '<div class="no-recommendations">
            <i class="far fa-sad-tear"></i>
            <h3>No recommendations yet</h3>
            <p>Rent a car or update your preferences to get personalized recommendations</p>
            <a href="#all-cars" class="explore-cars-btn">
                Explore All Cars <i class="fas fa-car"></i>
            </a>
        </div>';
    }
    ?>
</div>

<!-- All Available Cars Section -->
<section class="all-cars-section">
    <div class="container">
        <h2 class="section-title">All Available Cars</h2>
        
        <?php
        // Get all available cars
        $allCarsSql = "SELECT * FROM cars WHERE AVAILABLE = 'YES' ORDER BY PRICE ASC";
        $allCarsResult = $conn->query($allCarsSql);
        
        if ($allCarsResult->num_rows > 0) {
            echo '<div class="cars-grid">';
            while ($car = $allCarsResult->fetch_assoc()) {
                ?>
                <div class="car-card">
                    <div class="car-img">
                        <img src="images/<?php echo $car['CAR_IMG']; ?>" alt="<?php echo $car['CAR_NAME']; ?>">
                    </div>
                    <div class="car-info">
                        <h3 class="car-name"><?php echo $car['CAR_NAME']; ?></h3>
                        <div class="car-details">
                            <span><i class="fas fa-gas-pump"></i> <?php echo $car['FUEL_TYPE']; ?></span>
                            <span><i class="fas fa-users"></i> <?php echo $car['CAPACITY']; ?> Seater</span>
                        </div>
                        <div class="car-price">
                            <span class="price">₹<?php echo number_format($car['PRICE']); ?></span>
                            <span class="price-unit">per day</span>
                        </div>
                    </div>
                    <div class="car-actions">
                        <a href="booking.php?id=<?php echo $car['CAR_ID']; ?>" class="car-btn">Book Now</a>
                    </div>
                </div>
<?php 
if ($viewingSingleCar) {
?>
<style>
    .single-car-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }
        ?>
    </div>
</section>

<style>
    /* Enhanced styling for the All Cars section */
    .all-cars-section {
        padding: 50px 0;
        background-color: #f9f9f9;
    }
    
    .section-title {
        text-align: center;
        font-size: 2.2rem;
        color: #333;
        margin-bottom: 40px;
        position: relative;
    }
    
    .section-title::after {
        content: '';
        position: absolute;
        left: 50%;
        bottom: -10px;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, #4158D0, #C850C0);
    }
    
    .cars-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
    }
    
    .car-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .car-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    }
    
    .car-img {
        height: 200px;
        overflow: hidden;
    }
    
    .car-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .car-card:hover .car-img img {
        transform: scale(1.05);
    }
    
    .car-info {
        padding: 20px;
    }
    
    .car-name {
        font-size: 1.4rem;
        color: #333;
        margin-bottom: 12px;
    }
    
    .car-details {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
    }
    
    .car-details span {
        color: #666;
        font-size: 0.9rem;
    }
    
    .car-details i {
        color: #4158D0;
        margin-right: 5px;
    }
    
    .car-price {
        display: flex;
        align-items: baseline;
        margin-bottom: 15px;
    }
    
    .price {
        font-size: 1.5rem;
        font-weight: 600;
        color: #4158D0;
    }
    
    .price-unit {
        font-size: 0.9rem;
        color: #666;
        margin-left: 5px;
    }
    
    .car-actions {
        padding: 0 20px 20px;
    }
    
    .car-btn {
        display: block;
        width: 100%;
        padding: 12px;
        background: linear-gradient(45deg, #4158D0, #C850C0);
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 500;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        transition: opacity 0.3s ease;
    }
    
    .car-btn:hover {
        opacity: 0.9;
    }
    
    .no-cars-message {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 50px 20px;
        text-align: center;
    }
    
    .no-cars-message i {
        font-size: 3rem;
        color: #ccc;
        margin-bottom: 20px;
    }
    
    .no-cars-message p {
        color: #666;
        font-size: 1.1rem;
    }
    
    @media (max-width: 768px) {
        .cars-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        }
    }
</style>

<!-- Add this CSS in the head section of your document -->
<style>
    /* Recommended Section Styles */
    .recommended-section {
        padding: 3rem 0;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        margin: 2rem 0;
        border-radius: 10px;
        position: relative;
        overflow: hidden;
    }
    
    .recommended-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, #ff7e5f, #feb47b);
    }
    
    .recommended-section h2 {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 1.5rem;
        position: relative;
        display: inline-block;
    }
    
    .recommended-section h2::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 0;
        width: 50%;
        height: 3px;
        background: #ff7e5f;
    }
    
    .recommendation-subtitle {
        color: #666;
        margin-bottom: 2rem;
        font-size: 1.1rem;
    }
    
    .recommended-cars {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
        margin-top: 1.5rem;
    }
    
    .car-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
    }
    
    .car-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }
    
    .car-image-container {
        height: 200px;
        overflow: hidden;
        position: relative;
    }
    
    .car-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .car-card:hover .car-image {
        transform: scale(1.05);
    }
    
    .recommendation-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: linear-gradient(90deg, #ff7e5f, #feb47b);
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        z-index: 1;
        box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    }
    
    .car-details {
        padding: 20px;
    }
    
    .car-name {
        font-size: 1.4rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 0.5rem;
    }
    
    .car-meta {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
    }
    
    .car-spec {
        display: flex;
        align-items: center;
        color: #666;
        font-size: 0.9rem;
    }
    
    .car-spec i {
        margin-right: 5px;
        color: #ff7e5f;
    }
    
    .car-price {
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
        margin-top: 15px;
        display: flex;
        align-items: baseline;
    }
    
    .price-per-day {
        font-size: 0.9rem;
        color: #666;
        margin-left: 5px;
    }
    
    .book-now-btn {
        background: linear-gradient(90deg, #ff7e5f, #feb47b);
        color: white;
        border: none;
        border-radius: 25px;
        padding: 12px 20px;
        font-size: 0.9rem;
        font-weight: 600;
        width: 100%;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 15px;
    }
    
    .book-now-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 10px rgba(255, 126, 95, 0.3);
    }
    
    .book-now-btn i {
        margin-left: 8px;
    }
    
    .no-recommendations {
        text-align: center;
        padding: 3rem;
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .no-recommendations i {
        font-size: 3rem;
        color: #ccc;
        margin-bottom: 1rem;
    }
    
    .no-recommendations h3 {
        color: #555;
        margin-bottom: 1rem;
    }
    
    .no-recommendations p {
        color: #777;
        margin-bottom: 1.5rem;
    }
    
    .explore-cars-btn {
        background: linear-gradient(90deg, #ff7e5f, #feb47b);
        color: white;
        border: none;
        border-radius: 25px;
        padding: 12px 25px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: inline-flex;
        align-items: center;
    }
    
    .explore-cars-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 10px rgba(255, 126, 95, 0.3);
    }
    
    .explore-cars-btn i {
        margin-left: 8px;
    }
    
    @media (max-width: 768px) {
        .recommended-cars {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        }
    }
    
    @media (max-width: 576px) {
        .recommended-cars {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php
// Your existing car listing code continues...
// This part will only show if not viewing a single car
if (!$viewingSingleCar) {
    // Get filter parameters with sanitization
    $fuelType = isset($_GET['fuel_type']) ? htmlspecialchars($_GET['fuel_type']) : '';
    $capacity = isset($_GET['capacity']) ? (int)$_GET['capacity'] : '';
    $minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : '';
    $maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : '';
    $startDate = isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : '';
    $endDate = isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : '';

    // Build the SQL query with filters
    $sql = "SELECT * FROM cars WHERE 1=1 AND AVAILABLE = 'Y'"; // Only show available cars
    $params = array();
    $types = "";

    if (!empty($fuelType)) {
        $sql .= " AND FUEL_TYPE = ?";
        $params[] = $fuelType;
        $types .= "s";
    }

    if (!empty($capacity)) {
        $sql .= " AND CAPACITY >= ?";
        $params[] = $capacity;
        $types .= "i";
    }

    if (!empty($minPrice)) {
        $sql .= " AND PRICE >= ?";
        $params[] = $minPrice;
        $types .= "d";
    }

    if (!empty($maxPrice)) {
        $sql .= " AND PRICE <= ?";
        $params[] = $maxPrice;
        $types .= "d";
    }

    // Add date filtering if dates are provided
    if (!empty($startDate) && !empty($endDate)) {
        $sql .= " AND CAR_ID NOT IN (
            SELECT CAR_ID FROM bookings 
            WHERE (START_DATE BETWEEN ? AND ?) 
            OR (END_DATE BETWEEN ? AND ?)
            OR (START_DATE <= ? AND END_DATE >= ?)
        )";
        $params[] = $startDate;
        $params[] = $endDate;
        $params[] = $startDate;
        $params[] = $endDate;
        $params[] = $startDate;
        $params[] = $endDate;
        $types .= "ssssss";
    }

    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Display the filtered results
    if ($result->num_rows > 0) {
        echo '<div class="car-grid">';
        while ($row = $result->fetch_assoc()) {
            $carImage = !empty($row['CAR_IMG']) ? 'images/' . htmlspecialchars($row['CAR_IMG']) : 'images/default-car.jpg';
            ?>
            <div class="car-card">
                <div class="car-image-container">
                    <img src="<?php echo $carImage; ?>" alt="<?php echo htmlspecialchars($row['CAR_NAME']); ?>" class="car-image">
                </div>
                <div class="car-details">
                    <h3 class="car-name"><?php echo htmlspecialchars($row['CAR_NAME']); ?></h3>
                    <div class="car-meta">
                        <div class="car-spec">
                            <i class="fas fa-gas-pump"></i> <?php echo htmlspecialchars($row['FUEL_TYPE']); ?>
                        </div>
                        <div class="car-spec">
                            <i class="fas fa-users"></i> <?php echo (int)$row['CAPACITY']; ?> Seats
                        </div>
                    </div>
                    <div class="car-price">
                        ₹<?php echo number_format($row['PRICE'], 2); ?><span class="price-per-day">/day</span>
                    </div>
                    <a href="cardetails.php?id=<?php echo (int)$row['CAR_ID']; ?>" class="view-details-btn">
                        View Details <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    } else {
        echo '<div class="no-results">
                <h3>No cars found matching your criteria</h3>
                <p>Try adjusting your filters or browse our complete collection</p>
                <a href="cardetails.php" class="reset-filters-btn">Reset Filters</a>
              </div>';
    }
}

// Helper function to remove a single query parameter
function removeQueryParam($param) {
    $params = $_GET;
    unset($params[$param]);
    return '?' . http_build_query($params);
}

// Helper function to remove multiple query parameters
function removeQueryParams($paramArray) {
    $params = $_GET;
    foreach ($paramArray as $param) {
        unset($params[$param]);
    }
    return '?' . http_build_query($params);
}
?>

    </div> <!-- Close car-content -->
</div> <!-- Close single-car-container -->

<?php include('footer.php'); ?>

<script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
<script src="js/main.js"></script>
</body>
</html>
