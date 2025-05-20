<?php
session_start();

// 1) Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit();
}

include "connect.php";

// 2) Fetch the user’s profile picture
$user_id = $_SESSION['user_id'];
$query = "SELECT profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();
$conn->close();

// 3) Determine which image to show
$navbar_profile_pic = !empty($user_data['profile_pic'])
    ? $user_data['profile_pic']
    : 'images/user.png';

// 4) Load your about‑page content
include 'about-data.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>About Us - Tonishen's Kitchen</title>
    <link rel="stylesheet" href="about-style.css" />
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
                <li><a href="#">About</a></li>
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
                    background: #FF7750;;
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

    <main class="container">
        <section id="history" class="fade-in">
            <h2>Our History</h2>
            <p><?= nl2br(htmlspecialchars($store_history)) ?></p>
        </section>

        <section id="contact" class="fade-in">
            <h2>Contact Us</h2>
            <ul>
                <li><strong>Mobile:</strong> <?= htmlspecialchars($contact_info['mobile']) ?></li>
                <li><strong>Telephone:</strong> <?= htmlspecialchars($contact_info['telephone']) ?></li>
                <li><strong>Email:</strong> <a id="link" href="mailto:<?= htmlspecialchars($contact_info['email']) ?>"><?= htmlspecialchars($contact_info['email']) ?></a></li>
                <li><strong>Address:</strong> <?= htmlspecialchars($contact_info['address']) ?></li>
            </ul>
            <div class="social-media">
                <h3>Follow Us</h3>
                <ul>
                    <?php foreach ($social_media as $social): ?>
                        <li><a href="<?= htmlspecialchars($social['url']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($social['platform']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>

        <section id="faqs" class="fade-in">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-list">
                <?php foreach ($faqs as $faq): ?>
                    <details>
                        <summary><?= htmlspecialchars($faq['question']) ?></summary>
                        <p><?= htmlspecialchars($faq['answer']) ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="admin-note" class="fade-in">
            <p><em><?= htmlspecialchars($admin_note) ?></em></p>
        </section>
    </main>

    <script src="about-script.js"></script>
</body>
</html>
