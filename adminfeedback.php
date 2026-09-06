<?php
require_once('connection.php');
session_start();

// Check if admin is logged in
if (!isset($_SESSION['ADMIN'])) {
    header("Location: adminlogin.php");
    exit();
}

// Get all feedbacks with user details
$query = "SELECT f.*, u.FNAME, u.LNAME, u.EMAIL 
          FROM feedback f 
          JOIN users u ON f.email = u.EMAIL 
          ORDER BY f.feedback_date DESC";
$result = $conn->query($query);

// Calculate average rating
$avgQuery = "SELECT AVG(rating) as avg_rating FROM feedback";
$avgResult = $conn->query($avgQuery);
$avgRating = $avgResult->fetch_assoc()['avg_rating'];

// Get rating distribution
$ratingDistQuery = "SELECT rating, COUNT(*) as count FROM feedback GROUP BY rating ORDER BY rating";
$ratingDist = $conn->query($ratingDistQuery);
$ratingData = array_fill(1, 5, 0); // Initialize with 0 for all ratings
while ($row = $ratingDist->fetch_assoc()) {
    $ratingData[$row['rating']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Management - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4158D0;
            --secondary-color: #C850C0;
            --background: #f4f6f9;
            --text-color: #333;
            --light-text: #666;
            --border-color: #ddd;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: var(--background);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: var(--text-color);
            font-size: 2em;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            color: var(--light-text);
            font-size: 1em;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 2em;
            color: var(--primary-color);
            font-weight: 600;
        }

        .rating-bars {
            margin-top: 20px;
        }

        .rating-bar {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .stars {
            width: 100px;
            color: #ffd700;
        }

        .progress-bar {
            flex-grow: 1;
            height: 8px;
            background: #eee;
            border-radius: 4px;
            margin: 0 10px;
            overflow: hidden;
        }

        .progress {
            height: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 4px;
        }

        .count {
            width: 50px;
            text-align: right;
            color: var(--light-text);
        }

        .feedback-grid {
            display: grid;
            gap: 20px;
        }

        .feedback-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .user-info h3 {
            color: var(--text-color);
            font-size: 1.1em;
            margin-bottom: 5px;
        }

        .user-email {
            color: var(--light-text);
            font-size: 0.9em;
        }

        .feedback-date {
            color: var(--light-text);
            font-size: 0.9em;
        }

        .rating {
            color: #ffd700;
            margin-bottom: 10px;
        }

        .comment {
            color: var(--text-color);
            line-height: 1.6;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: white;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: var(--primary-color);
            color: white;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
                margin: 20px auto;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Feedback Management</h1>
            <a href="admindash.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <h3>Average Rating</h3>
                <div class="stat-value">
                    <?php echo number_format($avgRating, 1); ?>
                    <span style="font-size: 0.5em;">/5</span>
                </div>
                <div class="rating">
                    <?php
                    $fullStars = floor($avgRating);
                    $halfStar = $avgRating - $fullStars >= 0.5;
                    
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $fullStars) {
                            echo '<i class="fas fa-star"></i>';
                        } elseif ($i == $fullStars + 1 && $halfStar) {
                            echo '<i class="fas fa-star-half-alt"></i>';
                        } else {
                            echo '<i class="far fa-star"></i>';
                        }
                    }
                    ?>
                </div>
            </div>

            <div class="stat-card">
                <h3>Rating Distribution</h3>
                <div class="rating-bars">
                    <?php
                    $totalRatings = array_sum($ratingData);
                    for ($i = 5; $i >= 1; $i--) {
                        $percentage = $totalRatings > 0 ? ($ratingData[$i] / $totalRatings) * 100 : 0;
                    ?>
                        <div class="rating-bar">
                            <div class="stars">
                                <?php for ($j = 1; $j <= 5; $j++) {
                                    echo '<i class="' . ($j <= $i ? 'fas' : 'far') . ' fa-star"></i>';
                                } ?>
                            </div>
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <div class="count"><?php echo $ratingData[$i]; ?></div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="feedback-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($feedback = $result->fetch_assoc()): ?>
                    <div class="feedback-card">
                        <div class="feedback-header">
                            <div class="user-info">
                                <h3><?php echo htmlspecialchars($feedback['FNAME'] . ' ' . $feedback['LNAME']); ?></h3>
                                <span class="user-email"><?php echo htmlspecialchars($feedback['EMAIL']); ?></span>
                            </div>
                            <span class="feedback-date"><?php echo date('F j, Y', strtotime($feedback['feedback_date'])); ?></span>
                        </div>
                        <div class="rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?php echo $i <= $feedback['rating'] ? '' : '-o'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <div class="comment"><?php echo htmlspecialchars($feedback['comment']); ?></div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="feedback-card">
                    <p style="text-align: center; color: var(--light-text);">No feedback available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
