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

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Mark message as read
if (isset($_GET['mark_read']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "UPDATE contacts SET status = 'read' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admincontactus.php");
    exit();
}

// Delete message
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "DELETE FROM contacts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admincontactus.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Contact Messages</title>
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
            max-width: 1200px;
            margin: 80px auto 20px;
            padding: 20px;
        }

        .messages {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .message {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .message.unread {
            border-left: 4px solid #4158D0;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .message-header h3 {
            margin: 0;
            color: #333;
        }

        .date {
            color: #666;
            font-size: 0.9em;
        }

        .message-content {
            margin-bottom: 15px;
        }

        .message-text {
            margin-top: 10px;
            white-space: pre-line;
        }

        .message-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9em;
        }

        .btn-primary {
            background: #4158D0;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .no-messages {
            text-align: center;
            color: #666;
            padding: 20px;
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
        <h1>Contact Messages</h1>
        
        <div class="messages">
            <?php
            // Fetch all messages
            $sql = "SELECT * FROM contacts ORDER BY submission_date DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $statusClass = $row['status'] === 'unread' ? 'unread' : 'read';
                    ?>
                    <div class="message <?php echo $statusClass; ?>">
                        <div class="message-header">
                            <h3><?php echo htmlspecialchars($row['subject']); ?></h3>
                            <span class="date"><?php echo date('M j, Y g:i A', strtotime($row['submission_date'])); ?></span>
                        </div>
                        <div class="message-content">
                            <p><strong>From:</strong> <?php echo htmlspecialchars($row['name']); ?> (<?php echo htmlspecialchars($row['email']); ?>)</p>
                            <p class="message-text"><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
                        </div>
                        <div class="message-actions">
                            <?php if ($row['status'] === 'unread') { ?>
                                <a href="?mark_read=1&id=<?php echo $row['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-check"></i> Mark as Read
                                </a>
                            <?php } ?>
                            <a href="?delete=1&id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this message?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="no-messages">No messages found.</p>';
            }
            ?>
        </div>
    </div>
</body>
</html>
