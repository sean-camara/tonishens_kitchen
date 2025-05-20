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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <link rel="stylesheet" href="my-orders-style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
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
            <a href="cart.php" class="cart-wrapper">
                <i id="cart" class="fa-solid fa-cart-shopping fa-3x"></i>
                <span id="cart-count" style="display: none;">0</span>
            </a>
            <a href="profile.php"><img id="user" src="<?= $navbar_profile_pic ?>" alt="User image"></a>
        </div>
    </div>

    <div class="orders-container">
        <h1>My Orders</h1>
        <div id="orders-list" class="fade-in"></div>
    </div>

    <script>
        function fetchOrders() {
            fetch('get-user-orders.php')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('orders-list').innerHTML = data;
                })
                .catch(err => console.error("Error fetching orders:", err));
        }

        // Fetch orders every 5 seconds (live updates)
        fetchOrders();
        setInterval(fetchOrders, 20000);

        // [You can remove or ignore this if you no longer use a global-order feedback function]
        function submitFeedback(orderId) {
            const form = document.getElementById('feedback-form-' + orderId);
            const rating = form.querySelector('input[name="rating"]:checked');
            const comment = form.querySelector('textarea').value;

            if (!rating) {
                alert("Please select a star rating.");
                return;
            }

            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('rating', rating.value);
            formData.append('comment', comment);

            fetch('send-feedback.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(msg => {
                alert(msg);
                fetchOrders(); // Refresh orders after sending feedback
            });
        }

        // ————————————————
        // Add this function for dish-specific feedback
        function submitDishFeedback(orderId, dishId) {
            const form = document.getElementById('feedback-form-' + orderId + '-' + dishId);
            const ratingInput = form.querySelector(`input[name="rating-${orderId}_${dishId}"]:checked`);
            const commentInput = form.querySelector(`textarea[name="comment-${orderId}_${dishId}"]`);

            if (!ratingInput) {
                alert("Please select a star rating for this dish.");
                return;
            }

            const data = new FormData();
            data.append('order_id', orderId);
            data.append('dish_id', dishId);
            data.append('rating', ratingInput.value);
            data.append('comment', commentInput.value.trim());

            fetch('send-feedback.php', {
                method: 'POST',
                body: data
            })
            .then(res => res.text())
            .then(msg => {
                alert(msg);
                fetchOrders(); // Refresh orders to show updated feedback
            })
            .catch(err => {
                console.error("Feedback error:", err);
                alert("Something went wrong. Please try again.");
            });
        }
    </script>
</body>
</html>
