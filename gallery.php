<?php
require_once('connection.php');
session_start();

// Fetch all available cars
$query = "SELECT * FROM cars WHERE AVAILABLE = 'YES'";
$result = mysqli_query($conn, $query);
$cars = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get unique fuel types for filter
$types = array_unique(array_column($cars, 'FUEL_TYPE'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Gallery - CaRs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4158D0;
            --secondary-color: #C850C0;
            --text-color: #333;
            --light-text: #666;
            --white: #fff;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f9f9f9;
        }

        .gallery-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 60px 20px;
            text-align: center;
        }

        .gallery-header h1 {
            font-size: clamp(2em, 4vw, 3em);
            margin-bottom: 20px;
        }

        .gallery-header p {
            font-size: clamp(1em, 2vw, 1.2em);
            opacity: 0.9;
            max-width: 800px;
            margin: 0 auto;
        }

        .filters {
            background: white;
            padding: 20px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 8px 20px;
            border: none;
            background: none;
            color: var(--text-color);
            cursor: pointer;
            font-size: 1em;
            transition: var(--transition);
            border-radius: 25px;
        }

        .filter-btn:hover, .filter-btn.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .gallery-container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 20px;
            columns: 3;
            column-gap: 20px;
        }

        .car-card {
            break-inside: avoid;
            margin-bottom: 20px;
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
        }

        .car-image {
            width: 100%;
            display: block;
            border-radius: 10px;
            transition: var(--transition);
        }

        .car-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            padding: 20px;
            color: white;
            transform: translateY(100%);
            transition: var(--transition);
        }

        .car-card:hover .car-overlay {
            transform: translateY(0);
        }

        .car-card:hover .car-image {
            transform: scale(1.05);
        }

        .car-overlay h3 {
            font-size: 1.2em;
            margin-bottom: 10px;
        }

        .car-features {
            display: flex;
            gap: 15px;
            font-size: 0.9em;
            opacity: 0.9;
        }

        .car-features span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .view-details {
            display: inline-block;
            padding: 8px 20px;
            background: white;
            color: var(--primary-color);
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
            font-weight: 500;
            transition: var(--transition);
        }

        .view-details:hover {
            background: var(--primary-color);
            color: white;
        }

        @media (max-width: 1200px) {
            .gallery-container {
                columns: 2;
            }
        }

        @media (max-width: 768px) {
            .gallery-container {
                columns: 1;
            }

            .filters {
                padding: 15px;
            }

            .filter-btn {
                padding: 6px 15px;
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>
    <div class="gallery-header">
        <h1>Explore Our Collection</h1>
        <p>Discover our extensive fleet of premium vehicles. From luxurious sedans to powerful SUVs, find the perfect car for your journey.</p>
    </div>

    <div class="filters">
        <button class="filter-btn active" data-filter="all">All Cars</button>
        <?php foreach($types as $type): ?>
            <button class="filter-btn" data-filter="<?php echo strtolower($type); ?>"><?php echo $type; ?></button>
        <?php endforeach; ?>
    </div>

    <div class="gallery-container">
        <?php foreach($cars as $car): ?>
            <div class="car-card" data-type="<?php echo strtolower($car['FUEL_TYPE']); ?>">
                <img src="images/<?php echo $car['CAR_IMG']; ?>" alt="<?php echo $car['CAR_NAME']; ?>" class="car-image">
                <div class="car-overlay">
                    <h3><?php echo $car['CAR_NAME']; ?></h3>
                    <div class="car-features">
                        <span><i class="fas fa-car"></i> <?php echo $car['FUEL_TYPE']; ?></span>
                        <span><i class="fas fa-users"></i> <?php echo $car['CAPACITY']; ?> Seater</span>
                    </div>
                    <a href="cardetails.php?id=<?php echo $car['CAR_ID']; ?>" class="view-details">View Details</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        // Filter functionality
        const filterBtns = document.querySelectorAll('.filter-btn');
        const carCards = document.querySelectorAll('.car-card');

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove active class from all buttons
                filterBtns.forEach(b => b.classList.remove('active'));
                // Add active class to clicked button
                btn.classList.add('active');

                const filter = btn.dataset.filter;

                carCards.forEach(card => {
                    if (filter === 'all' || card.dataset.type === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>
