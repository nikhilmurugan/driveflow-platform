<?php
// Cancel booking logic goes here

include('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'];
    
    $stmt = $conn->prepare("DELETE FROM booking WHERE id = ?");
    $stmt->bind_param("i", $bookingId);
    
    if ($stmt->execute()) {
        header("Location: bookinstatus.php?success=1");
    } else {
        header("Location: bookinstatus.php?error=1");
    }
    exit();
}

header("Location: bookinstatus.php");
