<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$dish_id = $_POST['dish_id'] ?? null;

if (!$dish_id) {
    echo json_encode(['success' => false, 'message' => 'Dish ID missing']);
    exit();
}

include("connect.php");

// Example: Insert or update cart item for user in DB
// This depends on your DB schema. Here's a simple example:

// Check if dish already in cart
$stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND dish_id = ?");
$stmt->bind_param("ii", $user_id, $dish_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update quantity +1
    $row = $result->fetch_assoc();
    $new_qty = $row['quantity'] + 1;
    $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND dish_id = ?");
    $update->bind_param("iii", $new_qty, $user_id, $dish_id);
    $update->execute();
} else {
    // Insert new cart item
    $insert = $conn->prepare("INSERT INTO cart (user_id, dish_id, quantity) VALUES (?, ?, 1)");
    $insert->bind_param("ii", $user_id, $dish_id);
    $insert->execute();
}

// Get updated total count
$stmt = $conn->prepare("SELECT SUM(quantity) AS total FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$count = $row['total'] ?? 0;

echo json_encode(['success' => true, 'count' => $count]);
exit();
