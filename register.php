<?php
require_once('connection.php');

if(isset($_POST['regs'])) {
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $lic = mysqli_real_escape_string($conn, $_POST['lic']);
    $ph = mysqli_real_escape_string($conn, $_POST['ph']);
    $pass = mysqli_real_escape_string($conn, $_POST['pass']);
    $cpass = mysqli_real_escape_string($conn, $_POST['cpass']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    
    if(empty($fname) || empty($lname) || empty($email) || empty($lic) || empty($ph) || empty($pass) || empty($gender)) {
        $error = "Please fill in all fields";
    } else {
        if($pass == $cpass) {
            $sql2 = "SELECT * FROM users WHERE EMAIL=?";
            $stmt = mysqli_prepare($conn, $sql2);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            
            if(mysqli_num_rows($res) > 0) {
                $error = "Email already exists";
            } else {
                $Pass = md5($pass);
                $sql = "INSERT INTO users (FNAME, LNAME, EMAIL, LIC_NUM, PHONE_NUMBER, PASSWORD, GENDER) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sssssss", $fname, $lname, $email, $lic, $ph, $Pass, $gender);
                
                if(mysqli_stmt_execute($stmt)) {
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        } else {
            $error = "Passwords do not match";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CaRs Rental System</title>
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
            background: url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1920&q=80') center/cover;
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

        .register-container {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            width: 100%;
            max-width: 600px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-header h1 {
            color: white;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .register-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1em;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .input-group {
            flex: 1;
            position: relative;
        }

        .input-group label {
            display: block;
            color: white;
            margin-bottom: 5px;
            font-size: 0.9em;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 35px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.2em;
        }

        .input-group input {
            width: 100%;
            padding: 12px 15px 12px 45px;
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

        .gender-group {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        .gender-option {
            display: flex;
            align-items: center;
            gap: 5px;
            color: white;
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

        .register-btn {
            width: 100%;
            padding: 15px;
            background: #007aff;
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 1.1em;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .register-btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }

        .password-requirements {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.85em;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 10px;
            }

            .register-container {
                padding: 30px 20px;
            }

            .register-header h1 {
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
    
    <div class="register-container">
        <div class="register-header">
            <h1>Create Account</h1>
            <p>Join our community of car enthusiasts</p>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="register-form">
            <div class="form-row">
                <div class="input-group">
                    <label>First Name</label>
                    <i class="fas fa-user"></i>
                    <input type="text" name="fname" placeholder="John" required>
                </div>
                
                <div class="input-group">
                    <label>Last Name</label>
                    <i class="fas fa-user"></i>
                    <input type="text" name="lname" placeholder="Doe" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="input-group">
                    <label>Email</label>
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="john@example.com" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required>
                </div>
                
                <div class="input-group">
                    <label>Phone Number</label>
                    <i class="fas fa-phone"></i>
                    <input type="tel" name="ph" placeholder="1234567890" maxlength="10" pattern="[0-9]{10}" required>
                </div>
            </div>
            
            <div class="input-group">
                <label>License Number</label>
                <i class="fas fa-id-card"></i>
                <input type="text" name="lic" placeholder="Enter your license number" required>
            </div>
            
            <div class="form-row">
                <div class="input-group">
                    <label>Password</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" name="pass" id="password" placeholder="Create a strong password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required>
                    <div class="password-requirements">
                        Must contain at least 8 characters, including uppercase, lowercase, and numbers
                    </div>
                </div>
                
                <div class="input-group">
                    <label>Confirm Password</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" name="cpass" placeholder="Confirm your password" required>
                </div>
            </div>
            
            <div class="input-group">
                <label>Gender</label>
                <div class="gender-group">
                    <label class="gender-option">
                        <input type="radio" name="gender" value="male" required>
                        <span>Male</span>
                    </label>
                    <label class="gender-option">
                        <input type="radio" name="gender" value="female" required>
                        <span>Female</span>
                    </label>
                    <label class="gender-option">
                        <input type="radio" name="gender" value="other" required>
                        <span>Other</span>
                    </label>
                </div>
            </div>
            
            <button type="submit" name="regs" class="register-btn">Create Account</button>
        </form>
    </div>

    <script>
        function onlyNumberKey(evt) {
            var ASCIICode = (evt.which) ? evt.which : evt.keyCode;
            return (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57)) ? false : true;
        }
    </script>
</body>
</html>