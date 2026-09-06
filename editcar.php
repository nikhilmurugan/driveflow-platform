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

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: adminvehicle.php");
    exit();
}

$car_id = mysqli_real_escape_string($conn, $_GET['id']);

// Get car details
$sql = "SELECT * FROM cars WHERE CAR_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: adminvehicle.php");
    exit();
}

$car = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editcar'])) {
    $carname = mysqli_real_escape_string($conn, $_POST['carname']);
    $ftype = mysqli_real_escape_string($conn, $_POST['ftype']);
    $capacity = mysqli_real_escape_string($conn, $_POST['capacity']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);

    // Start with basic update query
    $update_sql = "UPDATE cars SET CAR_NAME = ?, FUEL_TYPE = ?, CAPACITY = ?, PRICE = ?";
    $types = "ssii";
    $params = array($carname, $ftype, $capacity, $price);

    // If new image is uploaded
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $image = $_FILES['image'];
        $image_name = $image['name'];
        $image_tmp = $image['tmp_name'];
        $image_size = $image['size'];
        $image_error = $image['error'];

        // Validate image
        $valid_extensions = array('jpg', 'jpeg', 'png');
        $file_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        if (in_array($file_extension, $valid_extensions) && $image_error === 0) {
            $new_image_name = uniqid('car_', true) . '.' . $file_extension;
            $upload_path = 'images/' . $new_image_name;

            if (move_uploaded_file($image_tmp, $upload_path)) {
                // Add image to update query
                $update_sql .= ", CAR_IMG = ?";
                $types .= "s";
                $params[] = $new_image_name;
            }
        }
    }

    // Complete the update query
    $update_sql .= " WHERE CAR_ID = ?";
    $types .= "i";
    $params[] = $car_id;

    // Prepare and execute the update
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        header("Location: adminvehicle.php?success=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car - CaRs Admin</title>
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
        }

        .navbar {
            background: white;
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
            font-weight: 600;
            color: #4158D0;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            text-decoration: none;
            color: #666;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #4158D0;
        }

        .container {
            max-width: 800px;
            margin: 6rem auto 2rem;
            padding: 0 1rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            color: #333;
            font-size: 2rem;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #4158D0;
        }

        .form-control::placeholder {
            color: #999;
        }

        .current-image {
            margin: 1rem 0;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 5px;
            text-align: center;
        }

        .current-image img {
            max-width: 200px;
            height: auto;
            border-radius: 5px;
        }

        .file-input {
            display: block;
            margin-top: 0.5rem;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-primary {
            background: #4158D0;
            color: white;
        }

        .btn-primary:hover {
            background: #3448a5;
        }

        .btn-secondary {
            background: #666;
            color: white;
        }

        .btn-secondary:hover {
            background: #555;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }

            .container {
                margin-top: 8rem;
            }

            .card {
                padding: 1.5rem;
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

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-car"></i> Edit Car</h1>
        </div>

        <div class="card">
            <form action="editcar.php?id=<?php echo $car_id; ?>" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="carname"><i class="fas fa-car"></i> Car Name</label>
                    <input type="text" class="form-control" id="carname" name="carname" value="<?php echo htmlspecialchars($car['CAR_NAME']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="ftype"><i class="fas fa-gas-pump"></i> Fuel Type</label>
                    <input type="text" class="form-control" id="ftype" name="ftype" value="<?php echo htmlspecialchars($car['FUEL_TYPE']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="capacity"><i class="fas fa-users"></i> Capacity</label>
                    <input type="number" class="form-control" id="capacity" name="capacity" min="1" value="<?php echo $car['CAPACITY']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="price"><i class="fas fa-rupee-sign"></i> Price per Day</label>
                    <input type="number" class="form-control" id="price" name="price" min="1" value="<?php echo $car['PRICE']; ?>" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-image"></i> Current Image</label>
                    <div class="current-image">
                        <img src="images/<?php echo htmlspecialchars($car['CAR_IMG']); ?>" alt="Current car image">
                    </div>
                    <label for="image">Upload New Image (optional)</label>
                    <input type="file" class="form-control file-input" id="image" name="image" accept="image/*">
                </div>

                <div class="actions">
                    <a href="adminvehicle.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button type="submit" name="editcar" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
