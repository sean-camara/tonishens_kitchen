<?php
// orders.php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: sign-in.php");
    exit();
}

include 'connect.php'; // ensures MySQL is set to UTC+8

// We’ll only need this page to output the HTML; JS will fetch data via AJAX.
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Manage Orders</title>
  <link 
    rel="stylesheet" 
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
  />
  <link rel="stylesheet" href="orders-style.css" />
  <link 
    href="https://fonts.googleapis.com/css2?family=Poppins&family=Playfair+Display&display=swap" 
    rel="stylesheet"
  />
</head>
<body>
  <div class="orders-container">
    <header class="orders-header">
      <h1>Order Management</h1>
      <a href="admin.php" class="back-btn">Back to Dashboard</a>
    </header>

    <!-- Filters & Bulk Actions -->
    <section class="orders-controls">
      <div class="filter-group">
        <label for="statusFilter">Status:</label>
        <select id="statusFilter">
          <option value="all">All</option>
          <option value="Pending">Pending</option>
          <option value="Preparing">Preparing</option>
          <option value="on-the-way">On the Way</option>
          <option value="Completed">Completed</option>
          <option value="Canceled">Canceled</option>
        </select>

        <label for="dateFrom">From:</label>
        <input type="date" id="dateFrom" />

        <label for="dateTo">To:</label>
        <input type="date" id="dateTo" />

        <button id="applyFilters">Apply Filters</button>
      </div>

      <div class="bulk-group">
        <label for="bulkStatus">Bulk Status:</label>
        <select id="bulkStatus">
          <option value="">Select...</option>
          <option value="Pending">Pending</option>
          <option value="Preparing">Preparing</option>
          <option value="On the Way">On the Way</option>
          <option value="Completed">Completed</option>
          <option value="Canceled">Canceled</option>
        </select>
        <button id="applyBulk" class="bulk-btn">Update Selected</button>
      </div>
    </section>

    <!-- Orders Table -->
    <section class="table-section">
      <table class="orders-table">
        <thead>
          <tr>
            <th><input type="checkbox" id="selectAll" /></th>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Date/Time</th>
            <th>Total</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="ordersTbody">
          <!-- JS will inject rows here -->
        </tbody>
      </table>
    </section>
  </div>

  <script src="orders.js"></script>
</body>
</html>
