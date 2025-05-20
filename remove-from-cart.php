<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'message'=>'Not logged in']);
    exit;
}
$user_id = $_SESSION['user_id'];
$id = $_GET['dish_id'] ?? null;
if (!$id) {
    echo json_encode(['success'=>false,'message'=>'Missing dish_id']);
    exit;
}

include 'connect.php';
$stmt = $conn->prepare("DELETE FROM cart WHERE user_id=? AND dish_id=?");
$stmt->bind_param("ii",$user_id,$id);
$stmt->execute();

echo json_encode(['success'=>true]);
