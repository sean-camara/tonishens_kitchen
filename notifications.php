<?php
session_start();
require 'connect.php';

// Only admins
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit;
}

// Fetch up to 10 pending orders from the last hour
$sql = "
  SELECT
    o.order_id,
    o.order_time,
    o.total_amount,
    GROUP_CONCAT(CONCAT(d.dish_name, ' x', oi.quantity) SEPARATOR ', ') AS summary
  FROM orders AS o
  JOIN order_items AS oi ON o.order_id = oi.order_id
  JOIN dishes AS d       ON oi.dish_id  = d.dish_id
  WHERE o.status = 'pending'
    AND o.order_time > (NOW() - INTERVAL 1 HOUR)
  GROUP BY o.order_id
  ORDER BY o.order_time DESC
  LIMIT 10
";

if (! $res = $conn->query($sql)) {
    http_response_code(500);
    echo json_encode(['error' => 'DB error']);
    exit;
}

$out = [];
while ($r = $res->fetch_assoc()) {
    $out[] = [
        'id'      => (int)$r['order_id'],
        'time'    => date('g:i A', strtotime($r['order_time'])),
        'amount'  => number_format($r['total_amount'], 2),
        'summary' => $r['summary']
    ];
}

header('Content-Type: application/json');
echo json_encode($out);
