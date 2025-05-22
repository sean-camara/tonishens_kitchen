<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: sign-in.php");
    exit();
}

require 'connect.php';

// 1) Get admin info, including profile_pic blob
$user_id = $_SESSION['user_id'];
$query = "SELECT fname, lname, email, profile_pic FROM users_admin WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($fname, $lname, $email, $profile_pic_blob);
$stmt->fetch();
$stmt->close();

// Convert blob to base64 data URI
$profile_pic_src = 'images/user.png';
if (!empty($profile_pic_blob)) {
    $profile_pic_src = 'data:image/jpeg;base64,' . base64_encode($profile_pic_blob);
}

// 2) Get total sales
$salesQuery = "SELECT SUM(total_amount) AS total_sales FROM orders";
$salesResult = $conn->query($salesQuery);
$totalSales = "₱0";
if ($salesResult && $sRow = $salesResult->fetch_assoc()) {
    $totalSales = "₱" . number_format($sRow['total_sales'], 2);
}

// 3) Get total orders
$orderCountQuery = "SELECT COUNT(*) AS total_orders FROM orders";
$orderResult = $conn->query($orderCountQuery);
$totalOrders = 0;
if ($orderResult && $oRow = $orderResult->fetch_assoc()) {
    $totalOrders = $oRow['total_orders'];
}

// 4) Get best-selling dish
$bestDishQuery = "
    SELECT d.dish_name, SUM(oi.quantity) AS total_sold
    FROM order_items oi
    JOIN dishes d ON oi.dish_id = d.dish_id
    GROUP BY d.dish_id
    ORDER BY total_sold DESC
    LIMIT 1";
$bestDish = "N/A";
$bestResult = $conn->query($bestDishQuery);
if ($bestResult && $bRow = $bestResult->fetch_assoc()) {
    $bestDish = $bRow['dish_name'];
}

// 5) Get recent order
$recentOrderText = "No recent orders";
$sql = "
SELECT 
  o.order_id,
  DATE_FORMAT(o.order_time, '%l:%i %p') AS order_time_formatted,
  GROUP_CONCAT(CONCAT(d.dish_name, ' ', oi.quantity, 'x') SEPARATOR ', ') AS dishes_ordered
FROM orders o
JOIN order_items oi ON o.order_id = oi.order_id
JOIN dishes d ON oi.dish_id = d.dish_id
GROUP BY o.order_id
ORDER BY o.order_time DESC
LIMIT 1";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $recentOrderText = htmlspecialchars($row['dishes_ordered']) . ' at ' . $row['order_time_formatted'];
}

// 6) Get low stock items (less than or equal to 20)
$lowStockQuery = "SELECT item_name, stock_count FROM inventory WHERE stock_count <= 20";
$lowStockItems = $conn->query($lowStockQuery);

// 7) Get recent feedback – pull the very latest feedback + author’s BLOB pic
$feedback_name    = '';
$feedback_email   = '';
$feedback_comment = '';
$feedback_rating  = 0;
$feedback_pic_src = 'images/user.png'; // fallback avatar

$fbQry = "
    SELECT
      f.comment,
      f.rating,
      u.fname       AS fb_fname,
      u.lname       AS fb_lname,
      u.email       AS fb_email,
      u.profile_pic AS fb_pic_blob
    FROM feedback f
    JOIN users   u ON f.user_id = u.id
    ORDER BY f.feedback_id DESC
    LIMIT 1
";

if ($fbRes = $conn->query($fbQry)) {
    if ($r = $fbRes->fetch_assoc()) {
        $feedback_comment = htmlspecialchars($r['comment']);
        $feedback_rating  = intval($r['rating']);
        $feedback_name    = htmlspecialchars($r['fb_fname'] . ' ' . $r['fb_lname']);
        $feedback_email   = htmlspecialchars($r['fb_email']);

        // if the blob column is non-null and non-empty, base64-encode it
        if ($r['fb_pic_blob'] !== null && strlen($r['fb_pic_blob']) > 0) {
            $feedback_pic_src = 'data:image/jpeg;base64,' . base64_encode($r['fb_pic_blob']);
        }
    }
}

// 8) Customer Favorites (Top 3 dishes by ratings count)
$favQuery = "
    SELECT d.dish_name, COUNT(f.rating) AS rating_count
    FROM feedback f
    JOIN dishes d ON f.dish_id = d.dish_id
    GROUP BY f.dish_id
    ORDER BY rating_count DESC
    LIMIT 3";
$favorites = $conn->query($favQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="admin-style.css" />
</head>
<body>
  <div class="container">
    <div class="nav">
      <div class="logo-wt">
        <img id="logo" src="images/logo.jpg" alt="logo" />
        <h3 id="logo-text">Tonishen's Kitchen</h3>
      </div>
      <div class="profile">
        <div class="mail-dropdown-wrapper">
          <button id="order-notif-btn" class="icon-btn">
            <i class="fa-solid fa-envelope fa-2x"></i>
            <span id="notif-badge" class="badge" style="display:none">0</span>
          </button>
          <div id="order-dropdown" class="dropdown-panel">
            <div id="order-list"></div>
            <div class="dropdown-footer">
              <a id="view-order" href="orders.php">View all orders ›</a>
            </div>
          </div>
        </div>
        <img id="profile-pic" src="<?php echo $profile_pic_src; ?>" alt="profile" />
        <div class="pro-des">
          <p id="prof-name"><?php echo htmlspecialchars($fname . ' ' . $lname); ?></p>
          <p>@<?php echo htmlspecialchars($email); ?></p>
        </div>
        <form action="logout.php" method="post">
          <button id="logout-btn">Logout</button>
        </form>
      </div>
    </div>

        <div class="category-con">
            <div class="category">
                <button id="dashboard"><i class="fa-solid fa-table-columns"></i> Dashboard</button>
                <button id="menu-btn"><i class="fa-solid fa-compass"></i> Menu</button>
                <button id="sales-report-btn"><i class="fa-solid fa-money-bill"></i> Sales Report</button>
                <button id="orders-btn"><i class="fa-solid fa-truck-fast"></i> Orders</button>
                <button id="top-selling-btn"><i class="fa-solid fa-chart-simple"></i> Top Selling</button>
                <button id="user-acc-btn"><i class="fa-solid fa-users"></i> User Account</button>
                <button id="inventory"><i class="fa-solid fa-toolbox"></i> Inventory</button>
                <button id="about-page-btn"><i class="fa-solid fa-info-circle"></i> About Page</button>
            </div>

            <div class="welcome-page">
                <p id="welcome-user">Hi <?php echo htmlspecialchars($fname); ?>,</p>
                <p id="overview">DashBoard Overview</p>

                <button id="add-dish-btn"><i class="fa-solid fa-plus"></i> ADD NEW DISH</button>

                <div class="boxes">
                    <div class="total-sales">
                        <p class="title">Total Sales</p>
                        <p id="sales"><?php echo $totalSales; ?></p>
                    </div>

                    <div class="best-seller">
                        <p class="title">Best Seller Dish</p>
                        <p>Top 1</p>
                        <p id="best-dish"><?php echo htmlspecialchars($bestDish); ?></p>
                    </div>

                    <div class="total-orders">
                        <p class="title">Total Orders</p>
                        <p id="orders"><?php echo $totalOrders; ?></p>
                    </div>

                    <div class="recent-order">
                        <p class="title">Recent Order</p>
                        <div class="recent-order-name">
                            <p><?php echo $recentOrderText; ?></p>
                        </div>
                    </div>

                    <div class="low-stock">
                        <p class="title">Low Stock Warning</p>
                        <table>
                        <?php 
                        if ($lowStockItems->num_rows > 0) {
                            while ($item = $lowStockItems->fetch_assoc()) {
                                echo "<tr><td>" . htmlspecialchars($item['item_name']) . "</td><td class='stock-count'>" . $item['stock_count'] . " left</td></tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2'>All stocks are sufficient</td></tr>";
                        }
                        ?>
                        </table>
                    </div>

                    <div class="customer-fb">
                        <p class="title">Customer Feedback</p>
                        <div class="rating-prof">
                            <img id="user-image-fb" src="<?php echo $feedback_pic_src; ?>" alt="<?php echo $feedback_name; ?>" />
                            <div class="rating-prof-details">
                                <p id="customer-fb-name"><?php echo $feedback_name; ?></p>
                                <p id="prof-email">@<?php echo $feedback_email; ?></p>
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa-solid fa-star" style="color: <?= $i <= $feedback_rating ? '#fbc531' : '#dcdde1'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        <div class="fb">
                            <p><?php echo $feedback_comment; ?></p>
                        </div>
                    </div>


                    <div class="customerf">
                        <p class="title">Customer Favorites</p>
                        <?php
                        if ($favorites && $favorites->num_rows > 0) {
                            $rank = 1;
                            while ($fav = $favorites->fetch_assoc()) {
                                echo "<p>$rank. " . htmlspecialchars($fav['dish_name']) . " <span class='ratings'>" . $fav['rating_count'] . " ratings</span></p>";
                                $rank++;
                            }
                        } else {
                            echo "<p>No favorites found</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="admin.js"></script>
</body>
</html>
