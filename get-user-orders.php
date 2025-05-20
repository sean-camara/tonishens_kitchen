<?php
session_start();
require 'connect.php';

$user_id = $_SESSION['user_id'];

// 1) Fetch this user’s orders
$sql_orders = "
    SELECT 
        o.order_id,
        o.order_time,
        o.total_amount,
        o.status
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.order_time DESC
";
$stmt_orders = $conn->prepare($sql_orders);
if (!$stmt_orders) {
    echo "SQL Error (orders): " . $conn->error;
    exit;
}
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();

while ($order = $result_orders->fetch_assoc()):
    $order_id   = (int)$order['order_id'];
    $order_time = htmlspecialchars($order['order_time']);
    $total_amt  = number_format($order['total_amount'], 2);
    $status     = htmlspecialchars($order['status']);
    $status_class = strtolower(str_replace(' ', '-', $order['status']));
?>
    <div class="order-card fade-in">
        <p><strong>Order #<?= $order_id ?></strong></p>
        <p>Time: <?= $order_time ?></p>
        <p>Total: ₱<?= $total_amt ?></p>
        <p>Status:
            <span class="<?= $status_class ?>">
                <?= $status ?>
            </span>
        </p>

        <!-- 2) Fetch all dishes in this order -->
        <?php
        $sql_items = "
            SELECT
                oi.dish_id,
                oi.quantity,
                d.dish_name
                /* d.image is stored as BLOB, so we’ll not directly echo it here */
            FROM order_items oi
            JOIN dishes d 
                ON oi.dish_id = d.dish_id
            WHERE oi.order_id = ?
        ";
        $stmt_items = $conn->prepare($sql_items);
        if (!$stmt_items) {
            echo "<p style='color:red;'>SQL Error (items): " . $conn->error . "</p>";
            continue;
        }
        $stmt_items->bind_param("i", $order_id);
        $stmt_items->execute();
        $result_items = $stmt_items->get_result();
        ?>

        <div class="dishes-list">
            <?php while ($item = $result_items->fetch_assoc()):
                $dish_id   = (int)$item['dish_id'];
                $dish_name = htmlspecialchars($item['dish_name']);
                $quantity  = (int)$item['quantity'];

                // 3) Look up existing feedback for this user/order/dish
                $sql_feedback = "
                    SELECT rating, comment
                    FROM feedback
                    WHERE user_id = ?
                      AND order_id = ?
                      AND dish_id = ?
                    LIMIT 1
                ";
                $stmt_fb = $conn->prepare($sql_feedback);
                $stmt_fb->bind_param("iii", $user_id, $order_id, $dish_id);
                $stmt_fb->execute();
                $res_fb = $stmt_fb->get_result();
                $existing_rating  = null;
                $existing_comment = '';
                if ($fb_row = $res_fb->fetch_assoc()) {
                    $existing_rating  = (int)$fb_row['rating'];
                    $existing_comment = htmlspecialchars($fb_row['comment']);
                }
                $stmt_fb->close();
            ?>
                <div class="dish-item">
                    <div class="dish-info">
                        <!-- ← Corrected: point to dish_image.php so the BLOB is streamed -->
                        <img 
                            src="dish_image.php?dish_id=<?= $dish_id ?>" 
                            alt="<?= $dish_name ?>" 
                            class="dish-thumb"
                        >
                        <p class="dish-name"><?= $dish_name ?></p>
                        <p class="dish-qty">Quantity: <?= $quantity ?>×</p>
                    </div>

                    <!-- Dish‐specific feedback form -->
                    <form 
                        id="feedback-form-<?= $order_id ?>-<?= $dish_id ?>" 
                        class="feedback-form"
                        onsubmit="return false;"
                    >
                        <div class="star-rating">
                            <?php for ($i = 5; $i >= 1; $i--):
                                $checked = ($existing_rating === $i) ? "checked" : "";
                            ?>
                                <input 
                                    type="radio" 
                                    id="star-<?= $order_id ?>-<?= $dish_id ?>-<?= $i ?>" 
                                    name="rating-<?= $order_id ?>_<?= $dish_id ?>" 
                                    value="<?= $i ?>" 
                                    <?= $checked ?>
                                >
                                <label 
                                    for="star-<?= $order_id ?>-<?= $dish_id ?>-<?= $i ?>" 
                                    title="<?= $i ?> star<?= ($i > 1 ? 's' : '') ?>"
                                >★</label>
                            <?php endfor; ?>
                        </div>

                        <textarea 
                            name="comment-<?= $order_id ?>_<?= $dish_id ?>" 
                            placeholder="Leave a comment..." 
                            rows="2"
                        ><?= $existing_comment ?></textarea>
                        <br>

                        <button 
                            type="button"
                            onclick="submitDishFeedback(<?= $order_id ?>, <?= $dish_id ?>)"
                        >
                            Submit Feedback
                        </button>
                    </form>
                </div>
            <?php endwhile; 
            $stmt_items->close();
            ?>
        </div>
    </div>
<?php
endwhile;
$stmt_orders->close();
$conn->close();
?>
