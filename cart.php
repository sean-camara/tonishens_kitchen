<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit;
}
include 'connect.php';
$user_id = $_SESSION['user_id'];

// Handle batch removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_selected'])) {
    if (!empty($_POST['selected_items'])) {
        $del = $conn->prepare("DELETE FROM cart WHERE user_id=? AND dish_id=?");
        foreach ($_POST['selected_items'] as $dish_id) {
            $del->bind_param("ii", $user_id, $dish_id);
            $del->execute();
        }
    }
    header("Location: cart.php");
    exit;
}

// Fetch cart contents from DB
$sql = "
  SELECT c.dish_id, c.quantity, d.dish_name, d.price, d.image
    FROM cart c
    JOIN dishes d ON c.dish_id=d.dish_id
   WHERE c.user_id=?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$items = $res->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Your Cart</title>
  <link rel="stylesheet" href="cart-style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
</head>
<body>
  <div class="nav-bar">
    <a href="home.php"><div class="logo">
      <img id="logo-img" src="images/logo.jpg" alt="logo" />
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
  </div>

  <div class="back-btn">
    <a href="home-menu.php"><button id="back-btn">Back to Menu</button></a>
  </div>

  <div class="cart-container">
    <h2>Your Cart</h2>

    <?php if (!empty($items)): ?>
      <form method="POST" action="cart.php">
        <table>
          <thead>
            <tr>
              <th>
                <label for="select-all" class="select-all-wrapper">
                  <span>All</span>
                  <input type="checkbox" id="select-all" class="custom-checkbox" />
                  <span class="checkbox-box"></span>
                </label>
              </th>
              <th>Dish Image</th>
              <th>Dish</th>
              <th>Price</th>
              <th>Quantity</th>
              <th>Total</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item): ?>
              <?php
                $id = $item['dish_id'];
                $total = $item['price'] * $item['quantity'];
                $img_src = $item['image']
                  ? "data:image/jpeg;base64," . base64_encode($item['image'])
                  : "images/default-image.jpg";
              ?>
              <tr class="cart-row"
                  data-id="<?= $id ?>"
                  data-price="<?= $item['price'] ?>"
                  data-qty="<?= $item['quantity'] ?>">
                <td>
                  <input type="checkbox"
                         id="checkbox-<?= $id ?>"
                         name="selected_items[]"
                         value="<?= $id ?>"
                         class="custom-checkbox" />
                  <label for="checkbox-<?= $id ?>"></label>
                </td>
                <td><img src="<?= $img_src ?>"
                         alt="<?= htmlspecialchars($item['dish_name']) ?>"
                         style="width:80px;height:60px;object-fit:cover;border-radius:6px;" /></td>
                <td><?= htmlspecialchars($item['dish_name']) ?></td>
                <td>₱<?= number_format($item['price'], 2) ?></td>
                <td>
                  <button class="qty-btn decrease" data-id="<?= $id ?>">−</button>
                  <span class="qty-value"><?= $item['quantity'] ?></span>
                  <button class="qty-btn increase" data-id="<?= $id ?>">+</button>
                </td>
                <td>₱<?= number_format($total, 2) ?></td>
                <td>
                <a href="#"
                  class="remove-item"
                  data-id="<?= $item['dish_id'] ?>"
                  title="Remove this item">
                  <i class="fas fa-trash-alt" style="color:red;"></i>
                </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div style="margin-top:15px;">
          <button id="remove-btn"
                  type="submit"
                  name="remove_selected"
                  onclick="return confirm('Remove selected items?')">
            Remove Items
          </button>
        </div>
      </form>
    <?php else: ?>
      <p class="cart-empty-txt">Your cart is empty. <a href="home-menu.php">Go to menu</a></p>
    <?php endif; ?>
  </div>

  <?php if (!empty($items)): ?>
    <form action="checkout.php" method="POST" id="checkout-form">
      <div class="totals-box">
        <h3>Order Summary</h3>
        <div class="totals-row"><span>Subtotal:</span><span id="subtotal">₱0.00</span></div>
        <div class="totals-row"><span>Sales Tax (12%):</span><span id="tax">₱0.00</span></div>
        <div class="totals-row"><span>Delivery Fee:</span><span id="delivery">₱50.00</span></div>
        <div class="totals-row grand"><span>Grand Total:</span><span id="grand-total"><strong>₱50.00</strong></span></div>

        <div id="selected-items-inputs"></div>
        <input type="hidden" name="subtotal"     id="subtotal-input"     value="0">
        <input type="hidden" name="tax"          id="tax-input"          value="0">
        <input type="hidden" name="delivery_fee" id="delivery-fee-input" value="50">
        <input type="hidden" name="grand_total"  id="grand-total-input"  value="0">

        <div class="checkout-btn">
          <button type="submit" id="checkout-btn">Proceed to Checkout</button>
        </div>
      </div>
    </form>
  <?php endif; ?>

  <script src="cart.js"></script>
</body>
</html>
