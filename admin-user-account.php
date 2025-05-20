<?php
include 'connect.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <link rel="stylesheet" href="includes/user-style.css">
</head>
<body>

    <div class="top-bar">
        <h1>User Accounts</h1>
        <button class="add-btn" onclick="alert('Modal coming soon!')">
            <i class="fa fa-plus"></i> Add Admin
        </button>
    </div>

    <?php include 'includes/user-table.php'; ?>

    <script src="includes/user-scripts.js"></script>
</body>
</html>
