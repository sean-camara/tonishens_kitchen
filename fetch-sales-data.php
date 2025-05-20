<?php
// fetch-sales-data.php
header('Content-Type: application/json');
include 'connect.php'; // your users_db connection

// 1) Read the filter (today/week/month/year/all) from POST
$filter = $_POST['filter'] ?? 'all';

// 2) Build WHERE condition based on $filter
switch ($filter) {
    case 'today':
        $where = "DATE(o.order_time) = CURDATE()";
        break;
    case 'week':
        // YEARWEEK with mode 1 = week starts Monday
        $where = "YEARWEEK(o.order_time, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    case 'month':
        $where = "MONTH(o.order_time) = MONTH(CURDATE()) AND YEAR(o.order_time) = YEAR(CURDATE())";
        break;
    case 'year':
        $where = "YEAR(o.order_time) = YEAR(CURDATE())";
        break;
    default: // 'all'
        $where = "1";
        break;
}

// =============================================================================
// PART A: total sales per dish (for Chart.js)
// =============================================================================
// Join orders (o) → order_items (oi) → dishes (d)
$salesSql = "
SELECT
    d.dish_id,
    d.dish_name,
    SUM(oi.quantity) AS total_quantity,
    SUM(oi.quantity * d.price) AS total_sales_amount
FROM orders o
JOIN order_items oi ON o.order_id = oi.order_id
JOIN dishes d ON oi.dish_id = d.dish_id
WHERE $where
GROUP BY d.dish_id
ORDER BY total_sales_amount DESC
";

$salesResult = $conn->query($salesSql);
$salesData = [];
while ($row = $salesResult->fetch_assoc()) {
    $salesData[] = [
        'dish_id'           => (int)$row['dish_id'],
        'dish_name'         => $row['dish_name'],
        'total_quantity'    => (int)$row['total_quantity'],
        'total_sales_amount'=> (float)$row['total_sales_amount'],
    ];
}

// =============================================================================
// PART B: list of orders (for the HTML table)
// =============================================================================
// Join orders (o) → order_details (od) → users (u) → order_items (oi) → dishes (d)
$orderSql = "
SELECT
    o.order_id,
    o.order_time,
    o.total_amount,
    od.payment_method,
    u.fname,
    u.lname,
    GROUP_CONCAT(CONCAT(d.dish_name, ' (x', oi.quantity, ')') SEPARATOR ', ') AS dishes_ordered
FROM orders o
JOIN order_details od ON o.order_id = od.order_id
JOIN users u ON o.user_id = u.id
JOIN order_items oi ON o.order_id = oi.order_id
JOIN dishes d ON oi.dish_id = d.dish_id
WHERE $where
GROUP BY o.order_id
ORDER BY o.order_time DESC
";

$orderResult = $conn->query($orderSql);
$orderList = [];
while ($row = $orderResult->fetch_assoc()) {
    $orderList[] = [
        'order_id'       => (int)$row['order_id'],
        'order_time'     => $row['order_time'],
        'total_amount'   => (float)$row['total_amount'],
        'payment_method' => $row['payment_method'],
        'customer_name'  => $row['fname'] . ' ' . $row['lname'],
        'dishes_ordered' => $row['dishes_ordered'],
    ];
}

// 3) Return JSON
echo json_encode([
    'salesData' => $salesData,
    'orderList' => $orderList
]);

$conn->close();
