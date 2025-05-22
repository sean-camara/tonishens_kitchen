<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit();
}

require("connect.php");

$user_id = $_SESSION['user_id'];

$query   = "SELECT profile_pic FROM users WHERE id = ?";
$stmt    = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result     = $stmt->get_result();
$user_data  = $result->fetch_assoc();

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
    <title>My Orders</title>

    <!-- Load FontAwesome CSS (as in home.php) -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
      integrity="sha512-…(copy integrity hash from home.php)…"
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />

    <!-- Your stylesheet -->
    <link rel="stylesheet" href="my-orders-style.css">
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
                <li><a href="about.php">About</a></li>
                <li><a href="my-orders.php">My Orders</a></li>
            </ul>
        </div>

        <div class="icons">
            <a href="cart.php" class="cart-wrapper" style="position: relative;">
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
            <a href="profile.php">
                <img id="user" src="<?= htmlspecialchars($navbar_profile_pic) ?>" alt="User image">
            </a>
        </div>
    </div>

    <div class="orders-container">
        <h1>My Orders</h1>
        <div id="orders-list" class="fade-in"></div>
    </div>

    <!-- Inline order‑fetch + feedback script -->
    <script>
        function fetchOrders() {
            fetch('get-user-orders.php')
                .then(r => r.text())
                .then(html => {
                    document.getElementById('orders-list').innerHTML = html;
                })
                .catch(err => console.error("Error fetching orders:", err));
        }
        fetchOrders();
        setInterval(fetchOrders, 20000);

        function submitDishFeedback(orderId, dishId) {
            const form    = document.getElementById(`feedback-form-${orderId}-${dishId}`);
            const rating  = form.querySelector(`input[name="rating-${orderId}_${dishId}"]:checked`);
            const comment = form.querySelector(`textarea[name="comment-${orderId}_${dishId}"]`).value.trim();
            if (!rating) return alert("Please select a star rating for this dish.");

            const data = new FormData();
            data.append('order_id', orderId);
            data.append('dish_id', dishId);
            data.append('rating', rating.value);
            data.append('comment', comment);

            fetch('send-feedback.php', { method: 'POST', body: data })
                .then(r => r.text())
                .then(msg => { alert(msg); fetchOrders(); })
                .catch(() => alert("Something went wrong. Please try again."));
        }
    </script>

    <!-- 1) about‑page logic already here -->
    <!-- 2) Home’s cart‑count updater, to drive the orange badge -->
    <script src="home.js"></script>
</body>
</html>
