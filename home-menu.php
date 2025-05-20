<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit();
}

include("connect.php");

$user_id = $_SESSION['user_id'];

$query = "SELECT profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

$navbar_profile_pic = !empty($user_data['profile_pic']) ? $user_data['profile_pic'] : 'images/user.png';

// Fetch all dishes from database, ordered by dish_name ascending
$sql = "SELECT dish_id, dish_name, price, image, category, description FROM dishes ORDER BY dish_name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Menu</title>
    <link rel="stylesheet" href="home-menu-style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Playfair+Display&family=Poppins&display=swap" rel="stylesheet" />
    <link rel="icon" id="tab-logo" type="image/png" href="images/Ellipse 2.png" />
</head>
<body>
    <div class="nav-bar">
        <a href="home.php" style="text-decoration: none;">
            <div class="logo">
                <img id="logo-img" src="images/logo.jpg" alt="logo" />
                <h2>Tonishen's Kitchen</h2>
            </div>
        </a>

        <div class="nav-link">
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="home-menu.php">Menu</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="my-orders.php">My Orders</a></li>
            </ul>
        </div>

        <div class="icons">
            <a href="cart.php" style="position: relative;">
                <i id="cart" class="fa-solid fa-cart-shopping fa-3x"></i>
                <span id="cart-count" style="
                    position: absolute;
                    top: -10px;
                    right: -15px;
                    background: #FF7750;
                    color: white;
                    border-radius: 50%;
                    padding: 4px 8px;
                    font-size: 14px;
                    display: none;
                ">0</span>
            </a>
            <a href="profile.php"><img id="user" src="<?= $navbar_profile_pic ?>" alt="User image"></a>
        </div>
    </div>

    <div class="welcome-page">
        <h3>Tonishen's MENU</h3>
        <p id="category-paragraph">All Category</p>
    </div>

    <div class="category">
        <label for="category" style="font-family: 'Poppins', sans-serif; font-size: 18px;">Choose Category:</label>
        <select id="category" name="category" style="margin: 10px 0; padding: 8px 12px; font-family: 'Poppins', sans-serif; border-radius: 6px;">
            <option value="all">All</option>
            <option value="pork">Pork</option>
            <option value="beef">Beef</option>
            <option value="chicken">Chicken</option>
            <option value="seafood">Seafood</option>
            <option value="vegetable">Vegetable</option>
        </select>
    </div>

    <div class="card-container">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="card" data-category="<?= htmlspecialchars(strtolower($row['category'])) ?>" data-id="<?= $row['dish_id'] ?>" data-name="<?= htmlspecialchars($row['dish_name']) ?>" data-price="<?= $row['price'] ?>">
                    <?php if (!empty($row['image'])): ?>
                        <img class="dish-image" src="data:image/jpeg;base64,<?= base64_encode($row['image']) ?>" alt="<?= htmlspecialchars($row['dish_name']) ?>" />
                    <?php else: ?>
                        <img class="dish-image" src="images/default-dish.png" alt="Default Dish" />
                    <?php endif; ?>

                    <h4 class="dish-name"><?= htmlspecialchars($row['dish_name']) ?></h4>
                    <p class="price">₱<?= number_format($row['price'], 2) ?></p>
                    <div class="description"><?= htmlspecialchars($row['description']) ?></div>

                    <div class="btn-group">
                        <button type="button" class="buy-btn">BUY NOW</button>
                        <button type="button" class="cart-btn">ADD TO CART</button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No dishes found.</p>
        <?php endif; ?>
    </div>

    <script src="home-menu.js"></script>
</body>
</html>
