<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit();
}

include("connect.php");

$user_id = $_SESSION['user_id'];

// Fetch all dishes
$dishesQuery = "SELECT * FROM dishes ORDER BY dish_id ASC";
$dishesResult = mysqli_query($conn, $dishesQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Menu Dashboard</title>
    <link rel="stylesheet" href="admin-menu-style.css" />
</head>
<body>
    <div class="container">
        <h1>Menu Dashboard</h1>

        <!-- Add New Dish button -->
        <button class="add-button" onclick="window.location.href='admin-add-dish.php'">ADD NEW DISH</button>

        <!-- Back to Admin Dashboard button -->
        <button class="back-button" onclick="window.location.href='admin.php'">BACK TO DASHBOARD</button>

        <table>
            <thead>
                <tr>
                    <th class="head">Dish ID</th>
                    <th class="head">Dish Name</th>
                    <th class="head">Price (₱)</th>
                    <th class="head">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($dishesResult) > 0): ?>
                    <?php while($dish = mysqli_fetch_assoc($dishesResult)): ?>
                        <tr>
                            <td><?= htmlspecialchars($dish['dish_id']) ?></td>
                            <td><?= htmlspecialchars($dish['dish_name']) ?></td>
                            <td><?= number_format($dish['price'], 2) ?></td>
                            <td>
                                <a href="admin-edit-dish.php?id=<?= $dish['dish_id'] ?>" class="edit-button">Edit</a>
                                <a href="admin-delete-dish.php?id=<?= $dish['dish_id'] ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this dish?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center;">No dishes found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
