<?php
session_start();
include 'connect.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION['user_id']) || !$data) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized or invalid request']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = intval($data['order_id']);
$comment = trim($data['comment']);
$feedback = $data['feedback'] ?? [];

if (empty($feedback)) {
    echo json_encode(['success' => false, 'message' => 'No feedback provided']);
    exit;
}

// Prevent duplicate
$check = $conn->prepare("SELECT 1 FROM feedback WHERE user_id = ? AND dish_id IN (
    SELECT dish_id FROM order_items WHERE order_id = ?
)");
$check->bind_param("ii", $user_id, $order_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Feedback already submitted.']);
    exit;
}

// Insert feedback
$stmt = $conn->prepare("INSERT INTO feedback (user_id, dish_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");

foreach ($feedback as $item) {
    $dish_id = intval($item['dish_id']);
    $rating = intval($item['rating']);
    $stmt->bind_param("iiis", $user_id, $dish_id, $rating, $comment);
    $stmt->execute();
}

echo json_encode(['success' => true]);
