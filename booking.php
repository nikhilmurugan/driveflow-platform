<?php 
require_once('connection.php');
if (!isset($_SESSION)) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Check if car_id is provided
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$car_id = mysqli_real_escape_string($conn, $_GET['id']);
$email = $_SESSION['email'];

// Get parameters from cardetails.php if available
$pickup_location = isset($_GET['pickup_location']) ? urldecode($_GET['pickup_location']) : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d', strtotime('+3 days'));

// Get car details
$sql = "SELECT * FROM cars WHERE CAR_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$car = $result->fetch_assoc();

// Get user details
$sql = "SELECT * FROM users WHERE EMAIL = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Check if we have a stored dynamic price for this car and date range
$priceKey = $car_id . '_' . $start_date . '_' . $end_date;
$pricePerDay = isset($_SESSION['dynamic_prices'][$priceKey]) ? 
                $_SESSION['dynamic_prices'][$priceKey] : $car['PRICE'];

// If no dynamic price is stored, calculate it now
if (!isset($_SESSION['dynamic_prices'][$priceKey]) && function_exists('calculateDynamicPrice')) {
    // Check if the function exists in this context, if not we'll include it
    include_once('cardetails.php');
    $pricePerDay = calculateDynamicPrice(
        (float)$car['PRICE'],
        (int)$car_id,
        $start_date,
        $end_date,
        $conn,
        $pickup_location
    );
    // Store it in session
    $_SESSION['dynamic_prices'][$priceKey] = $pricePerDay;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_place = mysqli_real_escape_string($conn, $_POST['book_place']);
    $destination = mysqli_real_escape_string($conn, $_POST['destination']);
    $book_date = mysqli_real_escape_string($conn, $_POST['book_date']);
    $return_date = mysqli_real_escape_string($conn, $_POST['return_date']);
    $phone = isset($_POST['phone']) ? mysqli_real_escape_string($conn, $_POST['phone']) : $user['PHONE_NUMBER'];
    
    // Validate dates
    $today = date('Y-m-d');
    $start = new DateTime($book_date);
    $end = new DateTime($return_date);
    $now = new DateTime($today);
    
    // Check if dates are valid
    if ($start < $now) {
        $booking_error = "Booking date cannot be in the past. Please select a future date.";
    } elseif ($end <= $start) {
        $booking_error = "Return date must be after the booking date.";
    } else {
        // Calculate duration and price
        $duration = $start->diff($end)->days;
        
        if ($duration < 1) {
            $booking_error = "Minimum rental duration is 1 day.";
        } else {
            // Use the current dynamic price when dates change, or get from session
            $currentPriceKey = $car_id . '_' . $book_date . '_' . $return_date;
            $currentPricePerDay = isset($_SESSION['dynamic_prices'][$currentPriceKey]) ? 
                                $_SESSION['dynamic_prices'][$currentPriceKey] : $pricePerDay;
            
            // Calculate total price
            $price = $duration * $currentPricePerDay;
            
            // Check if the car is already booked for the requested dates
            $checkSql = "SELECT * FROM booking 
                        WHERE CAR_ID = ? 
                        AND BOOK_STATUS IN ('PENDING', 'APPROVED', 'CONFIRMED') 
                        AND (
                            (BOOK_DATE <= ? AND RETURN_DATE >= ?) OR 
                            (BOOK_DATE <= ? AND RETURN_DATE >= ?) OR
                            (BOOK_DATE >= ? AND RETURN_DATE <= ?)
                        )";
            
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("issssss", $car_id, $return_date, $book_date, $book_date, $book_date, $book_date, $return_date);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                // Car is already booked for these dates
                $booking_error = "Sorry, this car is not available for the selected dates. Please choose different dates or another car.";
            } else {
                // Insert booking with the dynamic price
                $sql = "INSERT INTO booking (CAR_ID, EMAIL, BOOK_PLACE, DESTINATION, BOOK_DATE, RETURN_DATE, DURATION, PRICE, BOOK_STATUS, PHONE_NUMBER) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'PENDING', ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isssssiis", $car_id, $email, $book_place, $destination, $book_date, $return_date, $duration, $price, $phone);
                
                if ($stmt->execute()) {
                    $booking_id = $stmt->insert_id;
                    header("Location: payment.php?booking_id=" . $booking_id);
                    exit();
                } else {
                    $booking_error = "An error occurred while processing your booking. Please try again.";
                }
            }
        }
    }
}

// For the car preview section, use the dynamic price
$priceToShow = $pricePerDay;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Car - CaRs</title>
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

        .car-preview {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: sticky;
            top: 100px;
        }

        .car-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .car-details h2 {
            color: #333;
            margin-bottom: 1rem;
        }

        .car-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-item {
            color: #666;
        }

        .info-item strong {
            display: block;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .price-tag {
            background: #4158D0;
            color: white;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .booking-form {
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
        }

        .btn-primary {
            background: #4158D0;
            color: white;
            width: 100%;
        }

        .btn-primary:hover {
            background: #3448a5;
        }

        .user-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 2rem;
        }

        .user-info p {
            color: #666;
            margin-bottom: 0.5rem;
        }

        .user-info strong {
            color: #333;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Autocomplete styles */
        .autocomplete-container {
            position: relative;
            width: 100%;
        }
        
        .autocomplete-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 999;
            max-height: 200px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ddd;
            border-radius: 0 0 5px 5px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            display: none;
        }
        
        .autocomplete-results.show {
            display: block;
        }
        
        .autocomplete-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .autocomplete-item:hover,
        .autocomplete-item.selected {
            background-color: rgba(65, 88, 208, 0.05);
        }
        
        .autocomplete-item:last-child {
            border-bottom: none;
        }

        @media (max-width: 992px) {
            .container {
                grid-template-columns: 1fr;
            }

            .car-preview {
                position: static;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="car-preview">
            <img src="images/<?php echo htmlspecialchars($car['CAR_IMG']); ?>" alt="<?php echo htmlspecialchars($car['CAR_NAME']); ?>" class="car-image">
            <div class="car-details">
                <h2><?php echo htmlspecialchars($car['CAR_NAME']); ?></h2>
                <div class="car-info">
                    <div class="info-item">
                        <strong>Fuel Type</strong>
                        <?php echo htmlspecialchars($car['FUEL_TYPE']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Capacity</strong>
                        <?php echo htmlspecialchars($car['CAPACITY']); ?> Seater
                    </div>
                </div>
                <div class="price-tag">
                    ₹<?php echo number_format((float)$priceToShow); ?> per day
                </div>
            </div>
        </div>

        <div class="booking-form">
            <div class="form-header">
                <h1>Book Your Car</h1>
                <p>Fill in the details to complete your booking</p>
            </div>

            <div class="user-info">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['FNAME'] . ' ' . $user['LNAME']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['EMAIL']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['PHONE_NUMBER']); ?></p>
            </div>

            <form action="" method="POST" class="booking-form">
                <?php if (isset($booking_error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $booking_error; ?>
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="book_place"><i class="fas fa-map-marker-alt"></i> Pickup Location</label>
                    <div class="autocomplete-container">
                        <input type="text" class="form-control" id="book_place" name="book_place" 
                               value="<?php echo htmlspecialchars($pickup_location); ?>" required>
                        <div id="pickup-autocomplete-results" class="autocomplete-results"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="destination"><i class="fas fa-map-pin"></i> Destination</label>
                    <div class="autocomplete-container">
                        <input type="text" class="form-control" id="destination" name="destination" required>
                        <div id="destination-autocomplete-results" class="autocomplete-results"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="book_date"><i class="far fa-calendar-alt"></i> Pickup Date</label>
                    <input type="date" class="form-control" id="book_date" name="book_date" 
                           value="<?php echo htmlspecialchars($start_date); ?>" 
                           min="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="return_date"><i class="far fa-calendar-alt"></i> Return Date</label>
                    <input type="date" class="form-control" id="return_date" name="return_date" 
                           value="<?php echo htmlspecialchars($end_date); ?>" 
                           min="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Confirm Booking
                </button>
            </form>
        </div>
    </div>

    <script>
        // Date validation
        document.addEventListener('DOMContentLoaded', function() {
            const bookDateInput = document.getElementById('book_date');
            const returnDateInput = document.getElementById('return_date');
            
            // Set up autocomplete for location inputs
            setupAutocomplete('book_place', 'pickup-autocomplete-results');
            setupAutocomplete('destination', 'destination-autocomplete-results');
            
            bookDateInput.addEventListener('change', function() {
                returnDateInput.min = this.value;
                if (returnDateInput.value && new Date(returnDateInput.value) < new Date(this.value)) {
                    returnDateInput.value = this.value;
                }
            });
            
            returnDateInput.addEventListener('change', function() {
                if (bookDateInput.value && new Date(this.value) < new Date(bookDateInput.value)) {
                    this.value = bookDateInput.value;
                }
            });
            
            // Function to set up autocomplete for an input field
            function setupAutocomplete(inputId, resultsId) {
                const input = document.getElementById(inputId);
                const resultsContainer = document.getElementById(resultsId);
                let debounceTimer;
                let selectedIndex = -1;
                let results = [];
                
                // Add input event listener for autocomplete
                input.addEventListener('input', function() {
                    const query = this.value.trim();
                    
                    // Clear previous timer
                    clearTimeout(debounceTimer);
                    
                    // Hide results if input is empty
                    if (query.length < 2) {
                        resultsContainer.classList.remove('show');
                        return;
                    }
                    
                    // Set a debounce to avoid too many requests
                    debounceTimer = setTimeout(function() {
                        // Call the Nominatim API
                        const apiUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&addressdetails=1`;
                        
                        fetch(apiUrl)
                            .then(response => response.json())
                            .then(data => {
                                results = data;
                                displayResults(results, resultsContainer);
                            })
                            .catch(error => {
                                console.error('Autocomplete error:', error);
                            });
                    }, 300); // 300ms debounce time
                });
                
                // Handle keyboard navigation in autocomplete results
                input.addEventListener('keydown', function(e) {
                    if (!resultsContainer.classList.contains('show')) return;
                    
                    const resultItems = resultsContainer.querySelectorAll('.autocomplete-item');
                    
                    // Down arrow
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        selectedIndex = Math.min(selectedIndex + 1, resultItems.length - 1);
                        updateSelectedItem(resultItems);
                    } 
                    // Up arrow
                    else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        selectedIndex = Math.max(selectedIndex - 1, -1);
                        updateSelectedItem(resultItems);
                    } 
                    // Enter key
                    else if (e.key === 'Enter' && selectedIndex >= 0) {
                        e.preventDefault();
                        selectResult(results[selectedIndex], input, resultsContainer);
                    }
                    // Escape key
                    else if (e.key === 'Escape') {
                        resultsContainer.classList.remove('show');
                        selectedIndex = -1;
                    }
                });
                
                // Hide results when clicking outside
                document.addEventListener('click', function(e) {
                    if (!input.contains(e.target) && !resultsContainer.contains(e.target)) {
                        resultsContainer.classList.remove('show');
                        selectedIndex = -1;
                    }
                });
                
                // Function to update the selected item highlighting
                function updateSelectedItem(items) {
                    items.forEach((item, index) => {
                        if (index === selectedIndex) {
                            item.classList.add('selected');
                            // Scroll the selected item into view if needed
                            item.scrollIntoView({ block: 'nearest' });
                        } else {
                            item.classList.remove('selected');
                        }
                    });
                }
                
                // Function to display autocomplete results
                function displayResults(results, container) {
                    // Clear previous results
                    container.innerHTML = '';
                    selectedIndex = -1;
                    
                    // If no results, hide container
                    if (results.length === 0) {
                        container.classList.remove('show');
                        return;
                    }
                    
                    // Create result items
                    results.forEach((result, index) => {
                        const item = document.createElement('div');
                        item.className = 'autocomplete-item';
                        
                        // Try to extract city or relevant place name
                        let displayName = result.display_name;
                        
                        // Create shorter display formats when possible
                        if (result.address) {
                            // Try to get the most relevant part (city, town, etc.)
                            const placeComponents = [
                                result.address.city,
                                result.address.town,
                                result.address.village,
                                result.address.county,
                                result.address.state
                            ].filter(Boolean);
                            
                            if (placeComponents.length > 0) {
                                // Add country for context
                                const country = result.address.country || '';
                                displayName = `${placeComponents[0]}${country ? ', ' + country : ''}`;
                            }
                        }
                        
                        item.textContent = displayName;
                        
                        // Add click event
                        item.addEventListener('click', function() {
                            selectResult(result, input, container);
                        });
                        
                        container.appendChild(item);
                    });
                    
                    // Show the results container
                    container.classList.add('show');
                }
                
                // Function to select a result
                function selectResult(result, input, container) {
                    // Try to extract city or relevant place name
                    let displayName = result.display_name;
                    
                    // Create shorter display formats when possible
                    if (result.address) {
                        // Try to get the most relevant part (city, town, etc.)
                        const placeComponents = [
                            result.address.city,
                            result.address.town,
                            result.address.village,
                            result.address.county,
                            result.address.state
                        ].filter(Boolean);
                        
                        if (placeComponents.length > 0) {
                            displayName = placeComponents[0];
                        }
                    }
                    
                    input.value = displayName;
                    container.classList.remove('show');
                    
                    // Trigger change event
                    const event = new Event('change', { bubbles: true });
                    input.dispatchEvent(event);
                }
            }
        });
    </script>
</body>
</html>