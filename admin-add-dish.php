<?php
require_once 'connect.php';

$nameErr = $priceErr = $imageErr = $descErr = $catErr = "";
$dish_name = $price = $description = $category = "";
$successMsg = $errorMsg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate dish name
    if (empty(trim($_POST['dish_name']))) {
        $nameErr = "Dish name is required.";
    } else {
        $dish_name = trim($_POST['dish_name']);
    }

    // Validate price
    if (empty(trim($_POST['price']))) {
        $priceErr = "Price is required.";
    } elseif (!is_numeric($_POST['price']) || floatval($_POST['price']) < 0) {
        $priceErr = "Price must be a positive number.";
    } else {
        $price = floatval($_POST['price']);
    }

    // Validate description
    if (empty(trim($_POST['description']))) {
        $descErr = "Description is required.";
    } else {
        $description = trim($_POST['description']);
    }

    // Validate category
    if (empty(trim($_POST['category']))) {
        $catErr = "Category is required.";
    } else {
        $category = trim($_POST['category']);
    }

    // Validate image upload
    if (!isset($_FILES['dish_image']) || $_FILES['dish_image']['error'] !== UPLOAD_ERR_OK) {
        $imageErr = "Dish image is required.";
    } else {
        $allowedTypes = ['image/jpeg', 'image/png'];
        $fileType = mime_content_type($_FILES['dish_image']['tmp_name']);
        $fileSize = $_FILES['dish_image']['size']; // in bytes

        if (!in_array($fileType, $allowedTypes)) {
            $imageErr = "Only JPG and PNG images are allowed.";
        } elseif ($fileSize > 5 * 1024 * 1024) { // 5MB limit
            $imageErr = "Image size must be 5MB or less.";
        }
    }

    // If no errors, save to DB
    if (empty($nameErr) && empty($priceErr) && empty($imageErr) && empty($descErr) && empty($catErr)) {
        $imageData = file_get_contents($_FILES['dish_image']['tmp_name']);

        $stmt = $conn->prepare("INSERT INTO dishes (dish_name, description, category, price, image) VALUES (?, ?, ?, ?, ?)");
        if ($stmt === false) {
            die("Prepare failed: " . htmlspecialchars($conn->error));
        }

        $null = NULL;
        // Bind parameters: s = string, s = string, s = string, d = double (float), b = blob
        $stmt->bind_param("sssdb", $dish_name, $description, $category, $price, $null);

        // Send the image blob data (5th parameter, index 4)
        $stmt->send_long_data(4, $imageData);

        if ($stmt->execute()) {
            $successMsg = "Dish added successfully!";
            // Clear inputs after success
            $dish_name = $price = $description = $category = "";
        } else {
            $errorMsg = "Error adding dish: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Add New Dish</title>
    <link rel="stylesheet" href="admin-menu-style.css" />
    <style>
        /* Your existing CSS styles */
        body {
            color: #2d2d2d;
        }
        .form-container {
            max-width: 500px;
            margin: 40px auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 12px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin: 15px 0 5px;
            font-weight: 600;
        }
        input[type="text"], input[type="number"], input[type="file"], textarea {
            width: 475px;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        .error {
            color: #cc0000;
            font-size: 14px;
            margin-top: 4px;
        }
        .success {
            color: #2d862d;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .error-msg {
            color: #cc0000;
            font-weight: 600;
            margin-bottom: 15px;
        }
        button {
            margin-top: 20px;
            padding: 12px 25px;
            font-size: 16px;
            border: none;
            background-color: #FF7750;
            color: #FBF9F8;
            border-radius: 14px;
            cursor: pointer;
        }
        button:hover {
            background-color: #e0663e;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #FF7750;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add New Dish</h2>

        <?php if ($successMsg): ?>
            <p class="success"><?= htmlspecialchars($successMsg) ?></p>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <p class="error-msg"><?= htmlspecialchars($errorMsg) ?></p>
        <?php endif; ?>

        <form method="POST" action="admin-add-dish.php" enctype="multipart/form-data" novalidate>
            <label for="dish_name">Dish Name</label>
            <input type="text" id="dish_name" name="dish_name" value="<?= htmlspecialchars($dish_name) ?>" required />
            <?php if ($nameErr): ?><p class="error"><?= $nameErr ?></p><?php endif; ?>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" required><?= htmlspecialchars($description) ?></textarea>
            <?php if ($descErr): ?><p class="error"><?= $descErr ?></p><?php endif; ?>

            <label for="category">Category</label>
            <input type="text" id="category" name="category" value="<?= htmlspecialchars($category) ?>" required />
            <?php if ($catErr): ?><p class="error"><?= $catErr ?></p><?php endif; ?>

            <label for="price">Price (₱)</label>
            <input type="number" step="0.01" min="0" id="price" name="price" value="<?= htmlspecialchars($price) ?>" required />
            <?php if ($priceErr): ?><p class="error"><?= $priceErr ?></p><?php endif; ?>

            <label for="dish_image">Insert Image (JPG or PNG, max 5MB)</label>
            <input type="file" id="dish_image" name="dish_image" accept=".jpg,.jpeg,.png" required />
            <?php if ($imageErr): ?><p class="error"><?= $imageErr ?></p><?php endif; ?>

            <button type="submit">Add Dish</button>
        </form>

        <a href="admin-menu.php" class="back-link">&larr; Back to Menu Dashboard</a>
    </div>
</body>
</html>

