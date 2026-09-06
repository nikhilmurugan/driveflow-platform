<?php
require 'connection.php';
session_start();

if(isset($_POST['adlog'])) {
    $id = $_POST['adid'];
    $pass = $_POST['adpass'];
    
    if(empty($id) || empty($pass)) {
        $error = "Please fill in all fields";
    } else {
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
        
        $id = mysqli_real_escape_string($conn, $id);
        $pass = mysqli_real_escape_string($conn, $pass);
        
        $query = "SELECT * FROM admin WHERE ADMIN_ID=?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }
        
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if ($pass == $row['ADMIN_PASSWORD']) {
                $_SESSION['admin_login'] = true;
                header("Location: admindash.php");
                exit();
            } else {
                $error = "Invalid Password";
            }
        } else {
            $error = "Invalid Admin ID";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CaRs Rental System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            background: url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1920&q=80') center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1;
        }

        .back-to-home {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 100;
            text-decoration: none;
            color: white;
            background: rgba(255, 255, 255, 0.1);
            padding: 10px 20px;
            border-radius: 5px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .back-to-home:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .login-container {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.1);
            padding: 50px 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            width: 100%;
            max-width: 440px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h1 {
            color: white;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .login-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1em;
        }

        .input-group {
            margin-bottom: 30px;
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.2em;
        }

        .input-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: white;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.2);
        }

        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .error-message {
            background: rgba(255, 59, 48, 0.1);
            color: #ff3b30;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: #007aff;
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 1.1em;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 40px 20px;
            }

            .login-header h1 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <a href="index.php" class="back-to-home">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>
    
    <div class="login-container">
        <div class="login-header">
            <h1>Admin Login</h1>
            <p>Access the administration dashboard</p>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="adid" placeholder="Admin ID" required>
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="adpass" placeholder="Password" required>
            </div>
            
            <button type="submit" name="adlog" class="login-btn">Login</button>
        </form>
    </div>
</body>
</html>