<?php

header('Content-Type: application/json');
require 'connect.php';

// 1) Read filters from POST (if set)
$status_filter = $_POST['status'] ?? 'all'; 
$date_from = $_POST['date_from'] ?? '';
$date_to   = $_POST['date_to']   ?? '';

// 2) Build WHERE clause pieces
$conditions = [];

// a) Status filter
if ($status_filter !== 'all') {
    $conditions[] = "o.status = '" . $conn->real_escape_string($status_filter) . "'";
}

// b) Date range filter
if (!empty($date_from) && !empty($date_to)) {
    // Expecting YYYY-MM-DD format
    $from = $conn->real_escape_string($date_from) . " 00:00:00";
    $to   = $conn->real_escape_string($date_to) . " 23:59:59";
    $conditions[] = "o.order_time BETWEEN '$from' AND '$to'";
}

// Combine conditions
$where = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// 3) Query to fetch all relevant orders
$sql = "
SELECT 
  o.order_id,
  DATE_FORMAT(o.order_time, '%Y-%m-%d %H:%i:%s') AS order_time,
  o.total_amount,
  o.status,
  u.fname,
  u.lname
FROM orders o
JOIN users u ON o.user_id = u.id
$where
ORDER BY o.order_time DESC
";
$result = $conn->query($sql);

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = [
        'order_id'    => (int)$row['order_id'],
        'order_time'  => $row['order_time'],
        'total_amount'=> (float)$row['total_amount'],
        'status'      => $row['status'],
        'customer'    => $row['fname'] . ' ' . $row['lname']
    ];
}

echo json_encode(['orders' => $orders]);
$conn->close();
