<?php
// 1) Include your DB connection
require 'connect.php';

// 2) Build the same “top selling” query
$sql = "
    SELECT 
        d.dish_name, 
        SUM(oi.quantity) AS total_sold
    FROM order_items oi
    JOIN dishes d ON oi.dish_id = d.dish_id
    GROUP BY oi.dish_id
    ORDER BY total_sold DESC
";

// 3) Execute and check for errors
$result = $conn->query($sql);
if ($result === false) {
    // If the query failed, stop and show the error (for debugging)
    http_response_code(500);
    echo "Database query error: " . htmlspecialchars($conn->error);
    exit;
}

// 4) Send CSV headers so browser downloads rather than rendering HTML
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="top_selling.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// 5) Open “php://output” as a file handle and write rows
$output = fopen('php://output', 'w');

// 6) First row: column headers
fputcsv($output, ['Dish Name', 'Total Sold']);

// 7) Loop through the result set and write each row
while ($row = $result->fetch_assoc()) {
    // fputcsv will automatically escape commas, quotes, etc.
    fputcsv($output, [
        $row['dish_name'],
        $row['total_sold']
    ]);
}

// 8) Close the handle (optional)
fclose($output);

// 9) Close the DB result
$result->free();
$conn->close();
exit;
?>
