<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'message'=>'Not logged in']);
    exit;
}
$user_id = $_SESSION['user_id'];
$id = $_POST['id'] ?? null;
$action = $_POST['action'] ?? null;
if (!$id || !$action) {
    echo json_encode(['success'=>false,'message'=>'Missing parameters']);
    exit;
}

include 'connect.php';

// Fetch current qty
$stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id=? AND dish_id=?");
$stmt->bind_param("ii",$user_id,$id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows) {
    $row = $res->fetch_assoc();
    $qty = $row['quantity'] + ($action==='increase'?1:-1);
    if ($qty>0) {
        $upd = $conn->prepare("UPDATE cart SET quantity=? WHERE user_id=? AND dish_id=?");
        $upd->bind_param("iii",$qty,$user_id,$id);
        $upd->execute();
    } else {
        $del = $conn->prepare("DELETE FROM cart WHERE user_id=? AND dish_id=?");
        $del->bind_param("ii",$user_id,$id);
        $del->execute();
        $qty = 0;
    }
} else {
    echo json_encode(['success'=>false,'message'=>'Item not in cart']);
    exit;
}

// Return new qty
echo json_encode(['success'=>true,'quantity'=>$qty]);
