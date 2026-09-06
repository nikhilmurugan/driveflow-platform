<?php
require 'connection.php';

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $subject = mysqli_real_escape_string($conn, $_POST['subject']);
        $message = mysqli_real_escape_string($conn, $_POST['message']);
        $date = date('Y-m-d H:i:s');

        // Create contacts table if it doesn't exist
        $sql_create = "CREATE TABLE IF NOT EXISTS contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            subject VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            submission_date DATETIME NOT NULL,
            status VARCHAR(20) DEFAULT 'unread'
        )";
        
        if ($conn->query($sql_create) === FALSE) {
            throw new Exception("Error creating table: " . $conn->error);
        }

        // Insert the contact message
        $sql = "INSERT INTO contacts (name, email, phone, subject, message, submission_date) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("ssssss", $name, $email, $phone, $subject, $message, $date);
        
        if ($stmt->execute()) {
            echo "<script>
                    alert('Message sent successfully!');
                    window.location.href = 'contactus.html';
                  </script>";
        } else {
            throw new Exception("Error executing statement: " . $stmt->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo "<script>
                alert('Error: " . addslashes($e->getMessage()) . "');
                window.location.href = 'contactus.html';
              </script>";
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($conn)) {
            $conn->close();
        }
    }
} else {
    // If someone tries to access this file directly without POST data
    header("Location: contactus.html");
    exit();
}
?>
