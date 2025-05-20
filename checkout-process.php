<?php
// checkout-process.php

// 1) Force PHP to use Manila time
date_default_timezone_set('Asia/Manila');

session_start();
include 'connect.php'; // make sure connect.php sets MySQL to +08:00

header('Content-Type: application/json'); // Set response type to JSON

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fname = isset($_POST['fname']) ? trim($_POST['fname']) : '';
    $lname = isset($_POST['lname']) ? trim($_POST['lname']) : '';
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    $request_cutlery = isset($_POST['cutlery']) ? $_POST['cutlery'] : null;
    $payment_method = isset($_POST['payment']) ? $_POST['payment'] : '';
    $change_for = null;

    if ($payment_method == 'cod' && isset($_POST['change_for'])) {
        $change_for = trim($_POST['change_for']);
    }

    $items = isset($_POST['items']) ? $_POST['items'] : [];
    $subtotal = isset($_POST['subtotal']) ? floatval($_POST['subtotal']) : 0;
    $tax = isset($_POST['tax']) ? floatval($_POST['tax']) : 0;
    $delivery_fee = isset($_POST['delivery_fee']) ? floatval($_POST['delivery_fee']) : 0;
    $grand_total = isset($_POST['grand_total']) ? floatval($_POST['grand_total']) : 0;

    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    if ($user_id === null) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in to place an order.']);
        exit();
    }

    // 2) Use Manila-based current timestamp
    $order_time = date('Y-m-d H:i:s');

    // Insert into orders
    $sql_orders = "INSERT INTO orders (user_id, order_time, total_amount) VALUES (?, ?, ?)";
    $stmt_orders = $conn->prepare($sql_orders);
    if (!$stmt_orders) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit();
    }
    $stmt_orders->bind_param("isd", $user_id, $order_time, $grand_total);
    if (!$stmt_orders->execute()) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt_orders->error]);
        exit();
    }

    $order_id = $conn->insert_id;

    // Insert into order_details
    $sql_details = "INSERT INTO order_details 
        (order_id, fname, lname, mobile, address, notes, request_cutlery, payment_method, change_for) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_details = $conn->prepare($sql_details);
    if (!$stmt_details) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit();
    }
    $stmt_details->bind_param(
        "issssssss",
        $order_id,
        $fname,
        $lname,
        $mobile,
        $address,
        $notes,
        $request_cutlery,
        $payment_method,
        $change_for
    );
    if (!$stmt_details->execute()) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt_details->error]);
        exit();
    }

    // Insert each item into order_items
    foreach ($items as $dish_id => $item) {
        $quantity = intval($item['quantity']);

        $sql_item = "INSERT INTO order_items (order_id, dish_id, quantity) VALUES (?, ?, ?)";
        $stmt_item = $conn->prepare($sql_item);
        if (!$stmt_item) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            exit();
        }
        $stmt_item->bind_param("iii", $order_id, $dish_id, $quantity);
        if (!$stmt_item->execute()) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt_item->error]);
            exit();
        }
    }

    // Clear the user's cart after successful order insertion
    $stmt_clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    if ($stmt_clear_cart) {
        $stmt_clear_cart->bind_param("i", $user_id);
        $stmt_clear_cart->execute();
        $stmt_clear_cart->close();
    }

    // Everything succeeded—return success and new order_id
    echo json_encode(['success' => true, 'order_id' => $order_id]);
    exit();

} else {
    // Reject non-POST requests
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}
?>
