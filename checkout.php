<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");  // Redirect if user not logged in
    exit();
}

$user_name = $_SESSION['user_name']; // Get the user's name from the session

include("connect.php");

$user_id = $_SESSION['user_id'];

$query = "SELECT fname, lname, address, mobile, profile_pic FROM users WHERE id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

$fname = $user_data['fname'];
$lname = $user_data['lname'];
$address = $user_data['address'];
$mobile = $user_data['mobile'];
$profile_pic = $user_data['profile_pic'] ?? 'images/default-avatar.png'; // fallback

$navbar_profile_pic = !empty($user_data['profile_pic']) ? $user_data['profile_pic'] : 'images/user.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="checkout-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Playfair+Display&family=Poppins&display=swap" rel="stylesheet">
    <link rel="icon" id="tab-logo" type="image/png" href="images/Ellipse 2.png">
</head>
<body>

    <!-- Popup Modal -->
    <div id="order-popup" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; 
        background: rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000;">
      <div style="background:#fff; padding:20px; border-radius:10px; text-align:center; width:300px;">
          <h2>Order placed successfully!</h2>
          <button id="popup-ok-btn" style="padding:10px 20px; margin-top:15px;">OK</button>
      </div>
    </div>

    <?php
    $items = $_POST['items'] ?? [];
    $subtotal = $_POST['subtotal'] ?? 0;
    $tax = $_POST['tax'] ?? 0;
    $deliveryFee = $_POST['delivery_fee'] ?? 0;
    $grandTotal = $_POST['grand_total'] ?? 0;
    ?>

    <!-- Navigation Bar -->
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
            </ul>
        </div>

        <div class="icons">
            <i id="cart" class="fa-solid fa-cart-shopping fa-3x"></i>
            <a href="profile.php"><img id="user" src="<?= $navbar_profile_pic ?>" alt="User image"></a>
        </div>
    </div>

    <!-- Back Button -->
    <div class="back-btn">
        <a href="cart.php"><button id="back-btn">← Back</button></a>
    </div>

    <!-- Checkout Form -->
    <form id="checkout-form" action="checkout-process.php" method="POST">
        <div class="content-container">

            <!-- Order Summary -->
            <?php if (!empty($items)) : ?>
                <div class="checkout-box">
                    <h3>Your Order Summary</h3>
                    <?php foreach ($items as $id => $item): ?>
                        <p>
                            <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                            Quantity: <?= htmlspecialchars($item['quantity']) ?><br>
                            Price: ₱<?= number_format($item['price'], 2) ?><br>
                            Subtotal: ₱<?= number_format($item['price'] * $item['quantity'], 2) ?>
                        </p>
                        <hr>
                    <?php endforeach; ?>
                    <p><strong>Subtotal:</strong> ₱<?= number_format($subtotal, 2) ?></p>
                    <p><strong>Sales Tax:</strong> ₱<?= number_format($tax, 2) ?></p>
                    <p><strong>Delivery Fee:</strong> ₱<?= number_format($deliveryFee, 2) ?></p>
                    <p><strong>Grand Total:</strong> ₱<?= number_format($grandTotal, 2) ?></p>
                </div>
            <?php endif; ?>

            <!-- Hidden Inputs for Cart Data -->
            <?php foreach ($items as $id => $item): ?>
                <input type="hidden" name="items[<?= $id ?>][name]" value="<?= htmlspecialchars($item['name']) ?>">
                <input type="hidden" name="items[<?= $id ?>][price]" value="<?= $item['price'] ?>">
                <input type="hidden" name="items[<?= $id ?>][quantity]" value="<?= $item['quantity'] ?>">
            <?php endforeach; ?>

            <input type="hidden" name="subtotal" value="<?= $subtotal ?>">
            <input type="hidden" name="tax" value="<?= $tax ?>">
            <input type="hidden" name="delivery_fee" value="<?= $deliveryFee ?>">
            <input type="hidden" name="grand_total" value="<?= $grandTotal ?>">

            <!-- Contact Details -->
            <div class="checkout-box">
                <h3>Contact Details</h3>
                <label for="fname">First Name</label>
                <input type="text" name="fname" id="fname" value="<?= htmlspecialchars($fname) ?>"/>

                <label for="lname">Last Name</label>
                <input type="text" name="lname" id="lname" value="<?= htmlspecialchars($lname) ?>"/>

                <label for="mobile">Mobile Number</label>
                <input type="text" id="mobile" name="mobile" placeholder="+63" value="<?= htmlspecialchars($mobile) ?>" />
            </div>

            <!-- Delivery Address -->
            <div class="checkout-box">
                <h3>Deliver to</h3>
                <label for="address">Address</label>
                <input type="text" id="address" name="address" placeholder="Enter your delivery address" value="<?= htmlspecialchars($address) ?>" />
            </div>

            <!-- Notes to Rider/Staff -->
            <div class="checkout-box">
                <h3>Notes to rider/staff</h3>
                <textarea id="notes" name="notes" placeholder="Add notes.." rows="3"></textarea>
            </div>

            <!-- Request Cutlery -->
            <div class="checkout-box">
                <h3>Request Cutlery</h3>
                <label>
                    <input type="radio" name="cutlery" value="yes" required>
                    Please include cutlery.
                </label><br>
                <label>
                    <input type="radio" name="cutlery" value="no" required>
                    No need for cutlery.
                </label>
            </div>

            <!-- Payment -->
            <div class="checkout-box">
                <h3>Payment</h3>

                <!-- Cash on Delivery (only clickable option) -->
                <label>
                    <input type="radio" name="payment" value="cod" required> 
                    Cash on Delivery
                </label>
                <input type="text" name="change_for" placeholder="Change for amount (e.g. 1000)"><br><br>

                <!-- GCash (Disabled) -->
                <label style="color: red; cursor: not-allowed;">
                    <input type="radio" name="payment" value="gcash" disabled> 
                    GCash (Unavailable Coming soon)
                </label><br><br>

                <!-- Debit/Credit (Disabled) -->
                <label style="color: red; cursor: not-allowed;">
                    <input type="radio" name="payment" value="card" disabled> 
                    Debit/Credit (Unavailable Coming soon)
                </label>
            </div>

            <!-- Confirm Button -->
            <div class="confirm-btn">
                <button type="submit" id="confirm-btn">Confirm Order</button>
            </div>
        </div>
    </form>

    <script src="checkout.js"></script>
</body>
</html>
