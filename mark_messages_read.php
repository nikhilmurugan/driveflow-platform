<?php
require 'connection.php';
session_start();

if (!isset($_SESSION['email'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

$email = $_SESSION['email'];

// Mark all support messages to this user as read
$stmt = $conn->prepare("UPDATE chat_messages SET is_read = 1 WHERE user_email = ? AND is_support_reply = 1");
$stmt->bind_param("s", $email);
$result = $stmt->execute();

header('Content-Type: application/json');
if ($result) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to mark messages as read']);
}
?> 