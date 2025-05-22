<?php require 'connect.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Accounts</title>
  <link rel="stylesheet" href="includes/user-style.css">
  <script src="https://kit.fontawesome.com/yourkit.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include("includes/header.php"); ?>

<div class="container">
  <button onclick="window.history.back()" class="back-btn">← Back</button>

  <h1>Admin Accounts</h1>
  <div class="actions">
    <button id="addAdminBtn"><i class="fa-solid fa-user-plus"></i> Add Admin</button>
  </div>

  <?php include("includes/admin-table.php"); ?>
</div>

<?php include("includes/admin-form.php"); ?>

<script src="includes/admin-scripts.js"></script>
</body>
</html>
