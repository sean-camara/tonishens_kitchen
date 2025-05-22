<?php require 'connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sales Report</title>
  <link rel="stylesheet" href="sales-report-style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <div class="container">
    <header class="sr-header">
      <h1>Sales Report</h1>
      <a href="admin.php" class="back-btn">Back to Dashboard</a>
    </header>

    <section class="filter-section">
      <label for="timeFilter">Select Time Range:</label>
      <select id="timeFilter">
        <option value="today">Today</option>
        <option value="week">This Week</option>
        <option value="month">This Month</option>
        <option value="year">This Year</option>
        <option value="all">All Time</option>
      </select>
    </section>

    <section class="chart-section">
      <canvas id="salesChart"></canvas>
    </section>

    <section class="table-section">
      <h2 class="table-title">Order List</h2>
      <table class="sales-table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Date/Time</th>
            <th>Total</th>
            <th>Payment Method</th>
            <th>Dishes Ordered</th>
          </tr>
        </thead>
        <tbody id="reportTableBody">
          <!-- JS will inject rows here -->
        </tbody>
      </table>
    </section>

    <!-- NEW: Grand Total Section -->
    <section class="grandtotal-section">
      <h2>Grand Total: ₱<span id="grandTotal">0.00</span></h2>
    </section>

    <section class="export-section">
      <button id="exportBtn">Export to CSV</button>
    </section>
  </div>

  <script src="sales-report.js"></script>
</body>
</html>
