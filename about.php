<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: sign-in.php");
  exit();
}
require "connect.php";

// Nav-bar profile pic
$user_id = $_SESSION['user_id'];
$q = $conn->prepare("SELECT profile_pic FROM users WHERE id=?");
$q->bind_param("i", $user_id);
$q->execute();
$res = $q->get_result()->fetch_assoc();
$navbar_profile_pic = !empty($res['profile_pic'])
  ? 'data:image/jpeg;base64,' . base64_encode($res['profile_pic'])
  : 'images/users.png';

// Load dynamic about data
include 'about-data.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>About Us - Tonishen's Kitchen</title>
  <!-- FontAwesome -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
  <!-- External CSS -->
  <link rel="stylesheet" href="about-style.css" />
</head>
<body>
  <!-- NAVBAR -->
  <header class="nav-bar fade-in-down">
    <a href="home.php" class="logo-link">
      <div class="logo">
        <img id="logo-img" src="images/logo.jpg" alt="logo" />
        <h2>Tonishen's Kitchen</h2>
      </div>
    </a>
    <nav class="nav-link">
      <ul>
        <li><a href="home.php">Home</a></li>
        <li><a href="home-menu.php">Menu</a></li>
        <li><a href="about.php" class="active">About</a></li>
        <li><a href="my-orders.php">My Orders</a></li>
      </ul>
    </nav>
    <div class="icons">
      <a href="cart.php" class="cart-wrapper">
        <i id="cart" class="fa-solid fa-cart-shopping fa-3x"></i>
        <span id="cart-count">0</span>
      </a>
      <a href="profile.php">
        <img id="user" src="<?= htmlspecialchars($navbar_profile_pic) ?>" alt="User image" />
      </a>
    </div>
  </header>

  <main class="container">
    <!-- HISTORY SECTION -->
    <section id="history" class="fade-in">
      <h2>Our History</h2>
      <p><?= nl2br(htmlspecialchars($store_history)) ?></p>
    </section>

    <!-- CONTACT SECTION -->
    <section id="contact" class="fade-in">
      <h2>Contact Us</h2>
      <ul>
        <?php foreach ($contacts as $c): ?>
          <li>
            <strong><?= htmlspecialchars($c['type']) ?>:</strong>
            <?php if (strtolower($c['type']) === 'email'): ?>
              <a id="email-link" href="mailto:<?= htmlspecialchars($c['value']) ?>">
                <?= htmlspecialchars($c['value']) ?>
              </a>
            <?php else: ?>
              <?= htmlspecialchars($c['value']) ?>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="social-media">
        <h3>Follow Us</h3>
        <div class="social-buttons">
          <?php foreach ($social_media as $s): ?>
            <a
              class="social-btn"
              href="<?= htmlspecialchars($s['url']) ?>"
              target="_blank"
              rel="noopener"
            >
              <?= htmlspecialchars($s['platform']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- FAQ SECTION -->
    <section id="faqs" class="fade-in">
      <h2>FAQs</h2>
      <div class="faq-list">
        <?php foreach ($faqs as $f): ?>
          <details>
            <summary><?= htmlspecialchars($f['question']) ?></summary>
            <p><?= htmlspecialchars($f['answer']) ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- ADMIN NOTE -->
    <section id="admin-note" class="fade-in">
      <p><em><?= htmlspecialchars($admin_note) ?></em></p>
    </section>
  </main>

  <script src="about-script.js"></script>
  <script src="home.js"></script>
</body>
</html>
