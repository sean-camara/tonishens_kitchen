<?php
require_once 'connect.php';

$dish_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($dish_id > 0) {
    // Optional: Check if dish exists first (not necessary but cleaner)
    $checkStmt = $conn->prepare("SELECT dish_id FROM dishes WHERE dish_id = ?");
    $checkStmt->bind_param("i", $dish_id);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        // Dish exists, proceed to delete
        $checkStmt->close();

        $deleteStmt = $conn->prepare("DELETE FROM dishes WHERE dish_id = ?");
        $deleteStmt->bind_param("i", $dish_id);

        if ($deleteStmt->execute()) {
            $deleteStmt->close();
            // Redirect after successful deletion
            header("Location: admin-menu.php?msg=deleted");
            exit;
        } else {
            $deleteStmt->close();
            die("Error deleting dish: " . $conn->error);
        }
    } else {
        $checkStmt->close();
        // Dish not found, redirect back
        header("Location: admin-menu.php?msg=notfound");
        exit;
    }
} else {
    // Invalid ID, redirect back
    header("Location: admin-menu.php");
    exit;
}
