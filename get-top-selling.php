<?php
require 'connect.php';

$days = $_GET['days'] ?? 'all';
$dateCondition = "";

if (is_numeric($days)) {
    $days = intval($days);
    $dateCondition = "AND o.order_time >= NOW() - INTERVAL $days DAY";
}

// ────────────
// Fix the typo: use o.order_id (not o.ordr_id)
// ────────────
$sql = "
    SELECT 
      d.dish_name,
      d.image,
      SUM(oi.quantity) AS total_sold
    FROM order_items oi
    JOIN dishes d ON oi.dish_id = d.dish_id
    JOIN orders o ON oi.order_id = o.order_id
    WHERE 1=1 
      $dateCondition
    GROUP BY oi.dish_id
    ORDER BY total_sold DESC
    LIMIT 10
";

$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'dish_name'  => $row['dish_name'],
        'image'      => base64_encode($row['image'] ?? ''),
        'total_sold' => (int)$row['total_sold']
    ];
}

header('Content-Type: application/json');
echo json_encode($data);
exit;
?>
