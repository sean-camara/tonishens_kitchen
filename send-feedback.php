<?php
// send-feedback.php

session_start();
require 'connect.php';
header('Content-Type: text/plain');

if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized. Please log in.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Sanitize and fetch POST parameters
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$dish_id  = isset($_POST['dish_id'])  ? intval($_POST['dish_id'])  : 0;
$rating   = isset($_POST['rating'])   ? intval($_POST['rating'])   : 0;
$comment  = isset($_POST['comment'])  ? trim($_POST['comment'])    : '';

// Simple validation
if ($order_id <= 0 || $dish_id <= 0 || $rating < 1 || $rating > 5) {
    echo "Invalid feedback data.";
    exit();
}

// Prevent duplicate feedback for the same user/order/dish
$check = $conn->prepare("
    SELECT 1 
    FROM feedback 
    WHERE user_id = ? 
      AND order_id = ? 
      AND dish_id = ?
");
if (!$check) {
    echo "Database error: " . $conn->error;
    exit();
}
$check->bind_param("iii", $user_id, $order_id, $dish_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "You have already submitted feedback for this dish.";
    $check->close();
    exit();
}
$check->close();

// Insert new feedback row
$stmt = $conn->prepare("
    INSERT INTO feedback (
        user_id, 
        order_id, 
        dish_id, 
        rating, 
        comment, 
        created_at
    ) VALUES (?, ?, ?, ?, ?, NOW())
");
if (!$stmt) {
    echo "Database error: " . $conn->error;
    exit();
}
$stmt->bind_param("iiiis", $user_id, $order_id, $dish_id, $rating, $comment);

if ($stmt->execute()) {
    echo "Feedback submitted successfully!";
} else {
    echo "Error saving feedback: " . $stmt->error;
}
$stmt->close();
?>
