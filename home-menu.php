<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit();
}

require("connect.php");

$user_id = $_SESSION['user_id'];

// Fetch user's profile picture
$query = "SELECT profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

if (!empty($user_data['profile_pic'])) {
    $imgData = base64_encode($user_data['profile_pic']);
    $navbar_profile_pic = 'data:image/jpeg;base64,' . $imgData;
} else {
    $navbar_profile_pic = 'images/users.png';
}

// Fetch all dishes, ordered by name
$sql = "
  SELECT dish_id, dish_name, price, image, category, description 
  FROM dishes 
  ORDER BY dish_name ASC
";
$dishes = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Menu - Tonishen's Kitchen</title>
  <link rel="icon" type="image/png" href="images/Ellipse 2.png" />

  <!-- External CSS -->
  <link rel="stylesheet" href="home-menu-style.css" />

  <!-- FontAwesome for icons -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
  />

  <!-- Google Fonts -->
  <link
    href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Playfair+Display&family=Poppins&display=swap"
    rel="stylesheet"
  />
</head>
<body>
  <!-- NAVIGATION BAR -->
  <header class="nav-bar">
    <a href="home.php" class="logo">
      <img id="logo-img" src="images/logo.jpg" alt="logo" />
      <h2>Tonishen's Kitchen</h2>
    </a>

    <nav class="nav-link">
      <ul>
        <li><a href="home.php">Home</a></li>
        <li><a href="home-menu.php" class="active">Menu</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="my-orders.php">My Orders</a></li>
      </ul>
    </nav>

    <div class="icons">
      <a href="cart.php" class="cart-icon">
        <i class="fa-solid fa-cart-shopping fa-3x"></i>
        <span id="cart-count">0</span>
      </a>
      <a href="profile.php">
        <img id="user" src="<?= htmlspecialchars($navbar_profile_pic) ?>" alt="User image" />
      </a>
    </div>
  </header>

  <!-- PAGE TITLE -->
  <section class="welcome-page">
    <h3>Tonishen's Menu</h3>
    <p id="category-paragraph">All Categories</p>
  </section>

  <!-- CATEGORY FILTER -->
  <section class="category-filter">
    <label for="category-select">Choose Category:</label>
    <select id="category-select" name="category">
      <option value="all">All</option>
      <option value="pork">Pork</option>
      <option value="beef">Beef</option>
      <option value="chicken">Chicken</option>
      <option value="seafood">Seafood</option>
      <option value="vegetable">Vegetable</option>
      <option value="pasta">Pasta</option>
      <option value="extra">Extra</option>
      <option value="drinks">Drinks</option>
      <option value="rice-meal">Rice Meal</option>
    </select>
  </section>

  <!-- CARD CONTAINER -->
  <main class="card-container">
    <?php if ($dishes && $dishes->num_rows > 0): ?>
      <?php while ($row = $dishes->fetch_assoc()): ?>
        <article
          class="card fade-in-up"
          data-category="<?= htmlspecialchars(strtolower($row['category'])) ?>"
          data-id="<?= $row['dish_id'] ?>"
        >
          <?php if (!empty($row['image'])): ?>
            <img
              class="dish-image"
              src="data:image/jpeg;base64,<?= base64_encode($row['image']) ?>"
              alt="<?= htmlspecialchars($row['dish_name']) ?>"
            />
          <?php else: ?>
            <img
              class="dish-image"
              src="images/default-dish.png"
              alt="Default Dish"
            />
          <?php endif; ?>

          <div class="card-content">
            <h4 class="dish-name"><?= htmlspecialchars($row['dish_name']) ?></h4>
            <p class="price">₱<?= number_format($row['price'], 2) ?></p>
            <p class="description">
              <?= htmlspecialchars($row['description']) ?>
            </p>
          </div>

          <div class="btn-group">
            <button type="button" class="buy-btn">BUY NOW</button>
            <button type="button" class="cart-btn">ADD TO CART</button>
          </div>
        </article>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="no-dishes">No dishes found.</p>
    <?php endif; ?>
  </main>

  <!-- External JS -->
  <script src="home-menu.js"></script>
</body>
</html>
