<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header("Location: home.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['order_id']);

// Fetch user profile picture
$query = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user_data = $result->fetch_assoc();
$navbar_profile_pic = !empty($user_data['profile_pic']) ? $user_data['profile_pic'] : 'images/user.png';

// Check if feedback already exists for this order
$check = $conn->prepare("SELECT 1 FROM feedback WHERE user_id = ? AND dish_id IN (
    SELECT dish_id FROM order_items WHERE order_id = ?
)");
$check->bind_param("ii", $user_id, $order_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    header("Location: home.php");
    exit();
}

// Fetch ordered dishes
$sql = "
    SELECT d.dish_id, d.dish_name, d.image
    FROM order_items oi
    JOIN dishes d ON oi.dish_id = d.dish_id
    WHERE oi.order_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

$dishes = [];
while ($row = $result->fetch_assoc()) {
    $dishes[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feedback</title>
    <link rel="stylesheet" href="feedback-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Playfair+Display&family=Poppins&display=swap" rel="stylesheet">
    <link rel="icon" id="tab-logo" type="image/png" href="images/Ellipse 2.png">
</head>
<body>

    <div class="nav-bar">
        <a href="home.php" style="text-decoration: none;">
            <div class="logo">
                <img id="logo-img" src="images/logo.jpg" alt="logo">
                <h2>Tonishen's Kitchen</h2>
            </div>
        </a>

        <div class="nav-link">
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="home-menu.php">Menu</a></li>
                <li><a href="#">About</a></li>
            </ul>
        </div>

        <div class="icons">
            <a href="cart.php"><i id="cart" class="fa-solid fa-cart-shopping fa-3x"></i></a>
            <a href="profile.php"><img id="user" src="<?= $navbar_profile_pic ?>" alt="User image"></a>
        </div>
    </div>

    <div class="feedback-container">
        <h2>Your order has been placed.</h2>
        <p>Kindly wait for the staff to send you a text message on your mobile number.</p>

        <form id="feedback-form">
            <div class="dishes-container">
                <?php foreach ($dishes as $dish): ?>
                    <div class="dish" data-dish-id="<?= $dish['dish_id'] ?>">
                        <img src="dish_image.php?dish_id=<?= $dish['dish_id'] ?>" alt="<?= htmlspecialchars($dish['dish_name']) ?>">
                        <p><?= htmlspecialchars($dish['dish_name']) ?></p>
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star" data-value="<?= $i ?>">&#9734;</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <textarea id="global-comment" placeholder="Let us know what you think!"></textarea>
            <input type="hidden" name="order_id" value="<?= $order_id ?>">
            <button type="submit">Submit Feedback</button>
        </form>

        <div id="feedback-message" style="display:none;">Thank you for your feedback!</div>
    </div>

    <script src="feedback.js"></script>
</body>
</html>
