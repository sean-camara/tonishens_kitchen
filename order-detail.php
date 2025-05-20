<?php
// order-detail.php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: sign-in.php");
    exit();
}

include 'connect.php';

// Get order_id from query
if (!isset($_GET['order_id'])) {
    die("Missing order_id.");
}
$order_id = intval($_GET['order_id']);

// Fetch order header (orders + order_details + users)
$sqlOrder = "
SELECT 
  o.order_id,
  o.order_time,
  o.total_amount,
  o.status,
  od.fname,
  od.lname,
  od.mobile,
  od.address,
  od.notes,
  od.request_cutlery,
  od.payment_method,
  od.change_for
FROM orders o
JOIN order_details od ON o.order_id = od.order_id
WHERE o.order_id = ?
";
$stmt = $conn->prepare($sqlOrder);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$orderData = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$orderData) {
    die("Order not found.");
}

// Fetch order items
$sqlItems = "
SELECT 
  oi.dish_id,
  d.dish_name,
  d.price,
  oi.quantity
FROM order_items oi
JOIN dishes d ON oi.dish_id = d.dish_id
WHERE oi.order_id = ?
";
$stmt2 = $conn->prepare($sqlItems);
$stmt2->bind_param("i", $order_id);
$stmt2->execute();
$itemsResult = $stmt2->get_result();
$orderItems = [];
while ($row = $itemsResult->fetch_assoc()) {
    $orderItems[] = $row;
}
$stmt2->close();

// For printable view?
$printMode = isset($_GET['print']) && $_GET['print'] == '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Order #<?php echo $orderData['order_id']; ?></title>
  <link 
    rel="stylesheet" 
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
  />
  <link rel="stylesheet" href="order-detail-style.css" />
  <?php if ($printMode): ?>
    <link rel="stylesheet" href="order-detail-print.css" media="print" />
  <?php endif; ?>
  <script>
    // Only show print button if not already in print mode
    function triggerPrint() {
      window.print();
    }
  </script>
</head>
<body>
  <div class="detail-container">
    <header class="detail-header">
      <h1>Order #<?php echo $orderData['order_id']; ?></h1>
      <?php if (!$printMode): ?>
      <button class="action-btn print" onclick="triggerPrint()">
        <i class="fa-solid fa-print"></i> Print
      </button>
      <?php endif; ?>
    </header>

    <section class="order-info">
      <div>
        <p><strong>Customer:</strong> <?php echo htmlspecialchars($orderData['fname'] . ' ' . $orderData['lname']); ?></p>
        <p><strong>Mobile:</strong> <?php echo htmlspecialchars($orderData['mobile']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($orderData['address']); ?></p>
      </div>
      <div>
        <p><strong>Order Time:</strong> <?php echo date('Y-m-d H:i:s', strtotime($orderData['order_time'])); ?></p>
        <p><strong>Payment:</strong> <?php echo htmlspecialchars($orderData['payment_method']); 
            if ($orderData['payment_method'] === 'cod' && !empty($orderData['change_for'])) {
                echo " (Change for ₱" . htmlspecialchars($orderData['change_for']) . ")";
            }
        ?></p>

        <!-- Status dropdown (only if NOT print mode) -->
        <?php if (!$printMode): ?>
        <label for="statusSelect"><strong>Status:</strong></label>
        <select id="statusSelect">
          <?php
          $statuses = ['Pending','Preparing','On the Way','Completed','Canceled'];
          foreach ($statuses as $st) {
              $sel = $st === $orderData['status'] ? 'selected' : '';
              echo "<option value=\"$st\" $sel>$st</option>";
          }
          ?>
        </select>
        <button id="saveStatusBtn">Save Status</button>
        <?php else: ?>
          <p><strong>Status:</strong> <?php echo htmlspecialchars($orderData['status']); ?></p>
        <?php endif; ?>
      </div>
    </section>

    <section class="items-section">
      <h2>Order Items</h2>
      <table class="items-table">
        <thead>
          <tr>
            <th>Dish Name</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orderItems as $item): 
            $subtotal = $item['price'] * $item['quantity'];
          ?>
          <tr>
            <td><?php echo htmlspecialchars($item['dish_name']); ?></td>
            <td>₱<?php echo number_format($item['price'],2); ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td>₱<?php echo number_format($subtotal,2); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>

    <section class="notes-section">
      <h2>Notes</h2>
      <p><?php echo nl2br(htmlspecialchars($orderData['notes'])); ?></p>
    </section>

    <section class="total-section">
      <h2>Totals</h2>
      <p><strong>Grand Total:</strong> ₱<?php echo number_format($orderData['total_amount'],2); ?></p>
    </section>
  </div>

  <?php if (!$printMode): ?>
  <script>
    // Handle status save via AJAX
    document.getElementById('saveStatusBtn').addEventListener('click', async () => {
      const newStatus = document.getElementById('statusSelect').value;
      const data = new URLSearchParams();
      data.append('order_id', <?php echo $order_id; ?>);
      data.append('new_status', newStatus);

      try {
        const res = await fetch('update-order-status.php', {
          method: 'POST',
          body: data
        });
        const json = await res.json();
        if (json.success) {
          alert('Status updated successfully.');
          // Optionally refresh parent page or change badge color
        } else {
          alert('Failed to update: ' + json.message);
        }
      } catch (err) {
        console.error('Error updating status:', err);
      }
    });
  </script>
  <?php endif; ?>
</body>
</html>
