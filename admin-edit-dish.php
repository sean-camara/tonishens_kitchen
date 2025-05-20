<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit();
}

include("connect.php");

$user_id = $_SESSION['user_id'];

$dish_id = $_GET['id'] ?? null;
$nameErr = $priceErr = $imageErr = $descErr = $catErr = "";
$successMsg = $errorMsg = "";

// Fetch existing data
if ($dish_id) {
    $stmt = $conn->prepare("SELECT dish_name, description, category, price, image FROM dishes WHERE dish_id = ?");
    $stmt->bind_param("i", $dish_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $dish = $result->fetch_assoc();
    $stmt->close();
} else {
    $errorMsg = "No dish ID provided.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dish_name = trim($_POST['dish_name']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $price = trim($_POST['price']);
    $updateImage = false;
    $imageData = null;

    // Validation
    if (empty($dish_name)) {
        $nameErr = "Dish name is required.";
    }

    if (empty($description)) {
        $descErr = "Description is required.";
    }

    if (empty($category)) {
        $catErr = "Category is required.";
    }

    if (empty($price) || !is_numeric($price) || floatval($price) < 0) {
        $priceErr = "Valid price is required.";
    }

    // Handle image upload (optional)
    if (isset($_FILES['dish_image']) && $_FILES['dish_image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png'];
        $fileType = mime_content_type($_FILES['dish_image']['tmp_name']);
        $fileSize = $_FILES['dish_image']['size'];

        if (!in_array($fileType, $allowedTypes)) {
            $imageErr = "Only JPG and PNG images are allowed.";
        } elseif ($fileSize > 5 * 1024 * 1024) {
            $imageErr = "Image size must be 5MB or less.";
        } else {
            $imageData = file_get_contents($_FILES['dish_image']['tmp_name']);
            $updateImage = true;
        }
    }

    // Update in DB if no errors
    if (empty($nameErr) && empty($descErr) && empty($catErr) && empty($priceErr) && empty($imageErr)) {
        if ($updateImage) {
            $stmt = $conn->prepare("UPDATE dishes SET dish_name = ?, description = ?, category = ?, price = ?, image = ? WHERE dish_id = ?");
            $stmt->bind_param("sssdis", $dish_name, $description, $category, $price, $null, $dish_id);
            $null = null;
            $stmt->send_long_data(4, $imageData);
        } else {
            $stmt = $conn->prepare("UPDATE dishes SET dish_name = ?, description = ?, category = ?, price = ? WHERE dish_id = ?");
            $stmt->bind_param("sssdi", $dish_name, $description, $category, $price, $dish_id);
        }

        if ($stmt->execute()) {
            $successMsg = "Dish updated successfully!";
            if ($updateImage) $dish['image'] = $imageData;
            $dish['dish_name'] = $dish_name;
            $dish['description'] = $description;
            $dish['category'] = $category;
            $dish['price'] = $price;
        } else {
            $errorMsg = "Error updating dish: " . $conn->error;
        }
        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Dish</title>
    <link rel="stylesheet" href="admin-style.css">
    <style>
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
        input[type="text"], input[type="number"], input[type="file"] {
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
        img.preview {
            display: block;
            max-width: 100%;
            height: auto;
            margin-top: 10px;
            border-radius: 8px;
        }

        textarea {
            width: 475px;
            padding: 10px;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #ccc;
            resize: vertical;
            overflow-y: auto;
            max-height: 200px; /* Optional: Prevent textarea from growing too tall */
}
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Dish</h2>

        <?php if ($successMsg): ?>
            <p class="success"><?= htmlspecialchars($successMsg) ?></p>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <p class="error-msg"><?= htmlspecialchars($errorMsg) ?></p>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data" novalidate>
            <label for="dish_name">Dish Name</label>
            <input type="text" id="dish_name" name="dish_name" value="<?= htmlspecialchars($dish['dish_name']) ?>" required />
            <?php if ($nameErr): ?><p class="error"><?= $nameErr ?></p><?php endif; ?>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" required><?= htmlspecialchars($dish['description'] ?? '') ?></textarea>
            <?php if ($descErr): ?><p class="error"><?= $descErr ?></p><?php endif; ?>

            <label for="category">Category</label>
            <input type="text" id="category" name="category" value="<?= htmlspecialchars($dish['category'] ?? '') ?>" required />
            <?php if ($catErr): ?><p class="error"><?= $catErr ?></p><?php endif; ?>

            <label for="price">Price (₱)</label>
            <input type="number" step="0.01" min="0" id="price" name="price" value="<?= htmlspecialchars($dish['price']) ?>" required />
            <?php if ($priceErr): ?><p class="error"><?= $priceErr ?></p><?php endif; ?>

            <label for="dish_image">Change Image (optional)</label>
            <input type="file" id="dish_image" name="dish_image" accept=".jpg,.jpeg,.png" />
            <?php if ($imageErr): ?><p class="error"><?= $imageErr ?></p><?php endif; ?>

            <?php if (!empty($dish['image'])): ?>
                <label>Current Image:</label>
                <img class="preview" src="data:image/jpeg;base64,<?= base64_encode($dish['image']) ?>" alt="Current Dish Image" />
            <?php endif; ?>

            <button type="submit">Save Changes</button>
        </form>

        <a href="admin-menu.php" class="back-link">&larr; Back to Menu Dashboard</a>
    </div>
</body>
</html>