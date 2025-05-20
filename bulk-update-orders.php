<?php
// bulk-update-orders.php
header('Content-Type: application/json');
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$order_ids = $_POST['order_ids'] ?? [];
$new_status = isset($_POST['new_status']) 
    ? $conn->real_escape_string($_POST['new_status']) 
    : '';

if (empty($order_ids) || !$new_status) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters.']);
    exit;
}

// Prepare a comma-separated list of ints
$ids = array_map('intval', $order_ids);
$id_list = implode(',', $ids);

// Bulk update
$sql = "UPDATE orders SET status = '$new_status' WHERE order_id IN ($id_list)";
if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$conn->close();
