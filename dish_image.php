<?php
include 'connect.php';

if (!isset($_GET['dish_id'])) {
    http_response_code(400);
    exit("No dish ID provided.");
}

$dish_id = intval($_GET['dish_id']);

$stmt = $conn->prepare("SELECT image FROM dishes WHERE dish_id = ?");
$stmt->bind_param("i", $dish_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    http_response_code(404);
    exit("Image not found.");
}

$stmt->bind_result($imageData);
$stmt->fetch();

header("Content-Type: image/jpeg"); // adjust if it's PNG or other
echo $imageData;
