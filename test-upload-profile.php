<?php
require 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $image = $_FILES['profile_pic']['tmp_name'];

    if ($image) {
        $imgData = file_get_contents($image);
        $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $stmt->bind_param("si", $imgData, $id);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Profile picture updated successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error updating profile picture.</p>";
        }
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <label for="id">User ID:</label><br>
    <input type="number" name="id" required><br><br>

    <label for="profile_pic">Upload Image:</label><br>
    <input type="file" name="profile_pic" accept="image/*" required><br><br>

    <button type="submit">Upload</button>
</form>
