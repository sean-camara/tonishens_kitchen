<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");  // Redirect if user not logged in
    exit();
}

require("connect.php");

$user_id = $_SESSION['user_id'];

$query = "SELECT profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

if (!empty($user_data['profile_pic'])) {
    // Convert BLOB to base64 encoded string
    $imgData = base64_encode($user_data['profile_pic']);
    // Use the correct MIME type (assuming jpeg/png, adjust accordingly)
    $navbar_profile_pic = 'data:image/jpeg;base64,' . $imgData;
} else {
    $navbar_profile_pic = 'images/users.png'; // fallback image
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="home-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Playfair+Display&family=Poppins&display=swap" rel="stylesheet">
    <link rel="icon" id="tab-logo" type="image/png" href="images/Ellipse 2.png">
</head>

<body>
    <div class="nav-bar">
        <a href="home.php" style="text-decoration: none;"><div class="logo">
            <img id="logo-img" src="images/logo.jpg" alt="logo">
            <h2>Tonishen's Kitchen</h2>
        </div></a>

        <div class="nav-link">
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="home-menu.php">Menu</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="my-orders.php">My Orders</a></li>
            </ul>
        </div>

        <div class="icons">
            <a href="cart.php" class="cart-wrapper">
            <i id="cart" class="fa-solid fa-cart-shopping fa-3x"></i>
            <span id="cart-count" style="display: none;">0</span>
            </a>
            <a href="profile.php"><img id="user" src="<?= $navbar_profile_pic ?>" alt="User image"></a>
        </div>

    </div>

    <div class="hero">
        <div class="hero-text">
            <h1>Welcome to <br> Tonishen's Kitchen</h1>
            <p id="hero-des">Home made flavors made with <br> passion. Order now and enjoy!</p>
            <a href="home-menu.php"><button id="order-now-btn" class="button" name="">Order now</button></a>
        </div>

        <div class="hero-img">
            <img id="hero-img" src="images/hero-img.png" alt="Hero image">
        </div>
    </div>

    <div class="what-new">
        <div class="what-new-text">
            <h2 id="what-new-text">What's New?</h2>
        </div>

        <div class="what-new-content">
<?php
include ('connect.php');

$sql = "SELECT * FROM dishes ORDER BY dish_id DESC LIMIT 3";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo '
        <div class="card" data-id="' . $row["dish_id"] . '">
            <img src="data:image/jpeg;base64,' . base64_encode($row["image"]) . '" alt="Dish Image" class="dish-image" />
            <h2 class="dish-name">' . htmlspecialchars($row["dish_name"]) . '</h2>
            <p class="description">A delicious serving of ' . htmlspecialchars($row["dish_name"]) . ' for just <strong>₱' . htmlspecialchars($row["price"]) . '</strong>!</p>
            <div class="btn-group">
                <button class="buy-btn">Buy</button>
                <button class="cart-btn">Add to Cart</button>
            </div>
        </div>
        ';
    }
} else {
    echo "<p>No new dishes found.</p>";
}

$conn->close();
?>
</div>
    </div>

    <div class="about-container">
        <div class="about-us">
            <div id="about-us">
            <h2 id="abt">About us</h2>
            </div>

            <div class="about-container2">
                <div class="about-img">
                    <img id="about-img" src="images/About-img.jpg" alt="">
                </div>
                <div class="about-text">
                    <p id="about-welcome-text">Welcome to Tonishen's Kitchen!</p>
                    <br>
                    <p id="about-welcome-des">
                        At Tonishen's Kitchen, we believe that great food brings people together. We are passionate about creating delicious meals made with fresh ingredients, love, and a smile every single day.<br><br> Our kitchen is a place where tradition, passion, and flavor come alive.

                        Whether you're craving a hearty meal, a sweet treat, or just a cozy place to relax, Tonishen's Kitchen is here to serve you with heart. <br><br>
                        Every dish tells a story, and we can’t wait for you to be part of ours.
                        Come hungry, leave happy!
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="footer-logo">
            <img id="footer-logo-img" src="images/logo.jpg" alt="logo">
            <h2 id="h2-footer">Tonishen's Kitchen</h2>
        </div>

        <div class="footer-link">
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="home-menu.php">Menu</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="my-orders.php">My Orders</a></li>
            </ul>
        </div>
    </div>

    <script src="home.js"></script>
</body>
</html>