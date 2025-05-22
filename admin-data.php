<?php
require 'connect.php';

// 1. Total Sales
$totalSalesQuery = "SELECT SUM(total_amount) AS total_sales FROM orders";
$totalSalesResult = mysqli_query($conn, $totalSalesQuery);
$totalSales = mysqli_fetch_assoc($totalSalesResult)['total_sales'] ?? 0;

// 2. Total Orders
$totalOrdersQuery = "SELECT COUNT(*) AS total_orders FROM orders";
$totalOrdersResult = mysqli_query($conn, $totalOrdersQuery);
$totalOrders = mysqli_fetch_assoc($totalOrdersResult)['total_orders'] ?? 0;

// 3. Best Seller Dish
$bestSellerQuery = "
    SELECT d.dish_name, SUM(oi.quantity) AS total_sold
    FROM order_items oi
    JOIN dishes d ON oi.dish_id = d.dish_id
    GROUP BY oi.dish_id
    ORDER BY total_sold DESC
    LIMIT 1
";
$bestSellerResult = mysqli_query($conn, $bestSellerQuery);
$bestSeller = mysqli_fetch_assoc($bestSellerResult)['dish_name'] ?? 'N/A';

// 4. Recent Order
$recentOrderQuery = "
    SELECT d.dish_name, oi.quantity, o.order_time
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN dishes d ON oi.dish_id = d.dish_id
    ORDER BY o.order_time DESC
    LIMIT 1
";
$recentOrderResult = mysqli_query($conn, $recentOrderQuery);
$recentOrder = mysqli_fetch_assoc($recentOrderResult);

// Format order_time to “g:i A” if it exists
if (!empty($recentOrder['order_time'])) {
    $date = new DateTime($recentOrder['order_time']);
    $recentOrder['order_time'] = $date->format('g:i A');
} else {
    $recentOrder['order_time'] = 'N/A';
}

// 5. Low Stock Items
$lowStockQuery = "SELECT item_name, stock_count FROM inventory WHERE stock_count < 30";
$lowStockResult = mysqli_query($conn, $lowStockQuery);
$lowStocks = [];
while ($row = mysqli_fetch_assoc($lowStockResult)) {
    $lowStocks[] = $row;
}

// 6. Customer Feedback (most recent)
$feedbackQuery = "
    SELECT 
      f.rating, 
      f.comment, 
      u.fname, 
      u.lname, 
      u.email, 
      u.profile_pic
    FROM feedback f
    JOIN users u ON f.user_id = u.id
    ORDER BY f.feedback_id DESC
    LIMIT 1
";
$feedbackResult = mysqli_query($conn, $feedbackQuery);
$feedback = mysqli_fetch_assoc($feedbackResult);

// If there is feedback, Base64‐encode the BLOB profile_pic
if ($feedback) {
    if (!empty($feedback['profile_pic'])) {
        $feedback['profile_pic'] = base64_encode($feedback['profile_pic']);
    } else {
        $feedback['profile_pic'] = null;
    }
}

// 7. Customer Favorites (Top 4 Dishes by feedback count)
$favoritesQuery = "
    SELECT d.dish_name, COUNT(f.rating) AS ratings
    FROM feedback f
    JOIN dishes d ON f.dish_id = d.dish_id
    GROUP BY f.dish_id
    ORDER BY ratings DESC
    LIMIT 4
";
$favoritesResult = mysqli_query($conn, $favoritesQuery);
$favorites = [];
while ($row = mysqli_fetch_assoc($favoritesResult)) {
    $favorites[] = $row;
}

// Return all data as JSON
echo json_encode([
    'total_sales'  => $totalSales,
    'total_orders' => $totalOrders,
    'best_seller'  => $bestSeller,
    'recent_order' => $recentOrder,
    'low_stocks'   => $lowStocks,
    'feedback'     => $feedback,
    'favorites'    => $favorites
]);
?>
