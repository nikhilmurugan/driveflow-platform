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

// Handle availability toggle
if (isset($_POST['toggle_availability']) && isset($_POST['car_id'])) {
    $car_id = mysqli_real_escape_string($conn, $_POST['car_id']);
    $current_status = mysqli_real_escape_string($conn, $_POST['current_status']);
    
    // Toggle the status
    $new_status = ($current_status === 'Y') ? 'N' : 'Y';
    
    // Debug output
    error_log("Car ID: " . $car_id);
    error_log("Current Status: " . $current_status);
    error_log("New Status: " . $new_status);
    
    $sql = "UPDATE cars SET AVAILABLE = ? WHERE CAR_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $car_id);
    
    if ($stmt->execute()) {
        error_log("Update successful");
    } else {
        error_log("Update failed: " . $stmt->error);
    }
    
    header("Location: adminvehicle.php");
    exit();
}

// Search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sql = "SELECT *, 
        CASE WHEN AVAILABLE = 'Y' THEN 'Available' 
             WHEN AVAILABLE = 'N' THEN 'Unavailable' 
             ELSE AVAILABLE END as STATUS 
        FROM cars";
if (!empty($search)) {
    $sql .= " WHERE CAR_NAME LIKE ? OR FUEL_TYPE LIKE ? OR AVAILABLE LIKE ?";
}
$sql .= " ORDER BY CAR_ID DESC";

$stmt = $conn->prepare($sql);
if (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Vehicle Management</title>
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

        .add-btn {
            background: #007bff;
            color: #fff;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .add-btn:hover {
            background: #0056b3;
        }

        .vehicle-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .vehicle-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .vehicle-card:hover {
            transform: translateY(-5px);
        }

        .vehicle-image-container {
            width: 100%;
            height: 200px;
            overflow: hidden;
        }

        .vehicle-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .vehicle-details {
            padding: 1.5rem;
        }

        .vehicle-name {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .vehicle-info {
            margin-bottom: 1rem;
            color: #666;
        }

        .vehicle-price {
            font-size: 1.25rem;
            font-weight: 600;
            color: #007bff;
            margin-bottom: 1rem;
        }

        .vehicle-actions {
            display: flex;
            gap: 1rem;
        }

        .action-btn {
            flex: 1;
            padding: 0.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .edit-btn {
            background: #28a745;
            color: #fff;
        }

        .edit-btn:hover {
            background: #218838;
        }

        .delete-btn {
            background: #dc3545;
            color: #fff;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-available {
            background: #28a745;
            color: #fff;
        }

        .status-unavailable {
            background: #dc3545;
            color: #fff;
        }

        .search-container {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .search-form {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .search-input-group {
            flex: 1;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #eee;
            border-radius: 50px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .search-input:focus {
            outline: none;
            border-color: #4158D0;
            background: white;
            box-shadow: 0 0 0 3px rgba(65, 88, 208, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .search-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .search-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 50px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .search-btn.primary {
            background: #4158D0;
            color: white;
        }

        .search-btn.primary:hover {
            background: #3448a5;
        }

        .search-btn.secondary {
            background: #f8f9fa;
            color: #666;
        }

        .search-btn.secondary:hover {
            background: #e9ecef;
        }

        /* Toggle Switch Styles */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
            margin: 0.5rem 0;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #dc3545;
            transition: .4s;
            border-radius: 34px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        .toggle-switch input:checked + .toggle-slider {
            background-color: #28a745;
        }

        .toggle-switch input:checked + .toggle-slider:before {
            transform: translateX(30px);
        }

        .toggle-label {
            margin-left: 70px;
            font-weight: 500;
            color: #666;
        }

        .availability-container {
            display: flex;
            align-items: center;
            margin-top: 1rem;
        }

        .vehicle-status {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 1;
        }

        .vehicle-status.available {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .vehicle-status.unavailable {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }

            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .vehicle-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get all toggle checkboxes
            const toggles = document.querySelectorAll('input[name="toggle_availability"]');
            
            // Add click event listener to each toggle
            toggles.forEach(function(toggle) {
                toggle.addEventListener('change', function() {
                    this.closest('form').submit();
                });
            });
        });
    </script>
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
            <h1>Vehicle Management</h1>
            <a href="addcar.php" class="add-btn">
                <i class="fas fa-plus"></i> Add New Vehicle
            </a>
        </div>

        <div class="search-container">
            <form action="" method="GET" class="search-form">
                <div class="search-input-group">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" 
                           name="search" 
                           class="search-input" 
                           placeholder="Search cars by name, fuel type, or status..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           autocomplete="off">
                </div>
                <div class="search-buttons">
                    <button type="submit" class="search-btn primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="adminvehicle.php" class="search-btn secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="vehicle-grid">
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="vehicle-card">
                    <span class="vehicle-status <?php echo strtolower($row['STATUS']); ?>">
                        <?php echo htmlspecialchars($row['STATUS']); ?>
                    </span>
                    <div class="vehicle-image-container">
                        <img src="images/<?php echo htmlspecialchars($row['CAR_IMG']); ?>" alt="<?php echo htmlspecialchars($row['CAR_NAME']); ?>" class="vehicle-image">
                    </div>
                    <div class="vehicle-details">
                        <h2 class="vehicle-name"><?php echo htmlspecialchars($row['CAR_NAME']); ?></h2>
                        <div class="vehicle-info">
                            <p><i class="fas fa-car"></i> <?php echo htmlspecialchars($row['FUEL_TYPE']); ?></p>
                            <p><i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($row['CAPACITY']); ?></p>
                        </div>
                        <div class="vehicle-price">
                            $<?php echo number_format($row['PRICE'], 2); ?> / day
                        </div>
                        <div class="vehicle-actions">
                            <a href="editcar.php?id=<?php echo $row['CAR_ID']; ?>" class="btn btn-secondary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="car_id" value="<?php echo $row['CAR_ID']; ?>">
                                <input type="hidden" name="current_status" value="<?php echo $row['AVAILABLE']; ?>">
                                <label class="toggle-switch">
                                    <input type="checkbox" 
                                           <?php echo $row['AVAILABLE'] === 'Y' ? 'checked' : ''; ?>
                                           name="toggle_availability">
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">
                                    <?php echo $row['AVAILABLE'] === 'Y' ? 'Available' : 'Unavailable'; ?>
                                </span>
                            </form>
                            <a href="adminvehicle.php?delete=1&id=<?php echo $row['CAR_ID']; ?>" 
                               class="action-btn delete-btn" 
                               onclick="return confirm('Are you sure you want to delete this vehicle?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
<?php
mysqli_free_result($result);
mysqli_close($conn);
?>