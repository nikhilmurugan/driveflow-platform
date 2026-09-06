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

// Delete user if requested
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "DELETE FROM users WHERE EMAIL = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: adminusers.php");
    exit();
}

// Fetch all users
$sql = "SELECT * FROM users ORDER BY EMAIL";
$result = $conn->query($sql);

if (!$result) {
    die("Error fetching users: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - User Management</title>
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

        .search-box {
            display: flex;
            align-items: center;
            background: #fff;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .search-box input {
            border: none;
            outline: none;
            padding: 0.5rem;
            font-size: 1rem;
            width: 300px;
        }

        .search-box i {
            color: #666;
            margin-right: 0.5rem;
        }

        .users-table {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            width: 100%;
        }

        .users-table th,
        .users-table td {
            padding: 1rem;
            text-align: left;
        }

        .users-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #1a1a1a;
        }

        .users-table tr:not(:last-child) {
            border-bottom: 1px solid #eee;
        }

        .users-table tbody tr:hover {
            background: #f8f9fa;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.3s ease;
            margin: 0 0.25rem;
            display: inline-block;
        }

        .view-btn {
            background: #007bff;
            color: #fff;
        }

        .view-btn:hover {
            background: #0056b3;
        }

        .delete-btn {
            background: #dc3545;
            color: #fff;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        .user-email {
            color: #666;
        }

        .user-name {
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }

            .header {
                flex-direction: column;
                gap: 1rem;
            }

            .search-box {
                width: 100%;
            }

            .search-box input {
                width: 100%;
            }

            .users-table {
                display: block;
                overflow-x: auto;
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

    <!-- Main Content -->
    <div class="container">
        <div class="header">
            <h1>User Management</h1>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search users..." onkeyup="searchUsers()">
            </div>
        </div>

        <table class="users-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>License</th>
                    <th>Gender</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="user-name">
                            <?php echo $row['FNAME'] . ' ' . $row['LNAME']; ?>
                        </td>
                        <td class="user-email">
                            <i class="fas fa-envelope"></i> <?php echo $row['EMAIL']; ?>
                        </td>
                        <td>
                            <i class="fas fa-phone"></i> <?php echo $row['PHONE_NUMBER']; ?>
                        </td>
                        <td>
                            <i class="fas fa-id-card"></i> <?php echo $row['LIC_NUM']; ?>
                        </td>
                        <td>
                            <i class="fas fa-user"></i> <?php echo $row['GENDER']; ?>
                        </td>
                        <td>
                            <a href="viewuser.php?id=<?php echo urlencode($row['EMAIL']); ?>" class="action-btn view-btn">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="adminusers.php?delete=1&id=<?php echo urlencode($row['EMAIL']); ?>" 
                               class="action-btn delete-btn"
                               onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        function searchUsers() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.querySelector('.users-table');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const nameCol = rows[i].getElementsByTagName('td')[0];
                const emailCol = rows[i].getElementsByTagName('td')[1];
                if (nameCol && emailCol) {
                    const name = nameCol.textContent || nameCol.innerText;
                    const email = emailCol.textContent || emailCol.innerText;
                    if (name.toLowerCase().indexOf(filter) > -1 || email.toLowerCase().indexOf(filter) > -1) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            }
        }
    </script>
</body>
</html>