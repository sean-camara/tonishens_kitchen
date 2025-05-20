<!-- admin-top-selling.php -->
<?php
require 'connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Top Selling Dishes</title>
    <link rel="stylesheet" href="admin-top-selling-style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <!-- Back Button -->
        <div class="back-container">
            <a href="admin.php" class="back-btn">← Back to Dashboard</a>
        </div>

        <h1>Top Selling Dishes</h1>

        <!-- Filter Section -->
        <div class="filter-section">
            <label for="filter">Filter by:</label>
            <select id="filter">
                <option value="all">All Time</option>
                <option value="7">Last 7 Days</option>
                <option value="30">Last 30 Days</option>
                <option value="90">Last 90 Days</option>
            </select>

            <button id="export-btn">Export to CSV</button>
        </div>

        <!-- Top Selling Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Dish Name</th>
                        <th>Total Sold</th>
                    </tr>
                </thead>
                <tbody id="top-selling-body">
                    <!-- Rows get injected by JS -->
                </tbody>
            </table>
        </div>

        <!-- Chart Section -->
        <canvas id="topSellingChart" height="500" width="800"></canvas>
    </div>

    <script src="admin-top-selling.js"></script>
</body>
</html>
