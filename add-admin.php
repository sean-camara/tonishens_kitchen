<?php
session_start();
require 'connect.php';

// Show all errors while debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pull from POST
    $fname = $_POST['fname']   ?? '';
    $lname = $_POST['lname']   ?? '';
    $email = $_POST['email']   ?? '';
    $role  = 'admin';
    $pass  = $_POST['password'] ?? '';

    // Simple validation
    if (!$fname || !$lname || !$email || !$pass || empty($_FILES['profile_pic']['tmp_name'])) {
        die("All fields including profile picture & password are required.");
    }

    // Hash the password
    $hashed = password_hash($pass, PASSWORD_DEFAULT);

    // Read the picture into a blob
    $pic_blob = file_get_contents($_FILES['profile_pic']['tmp_name']);

    // Prepare INSERT into users_admin
    $sql = "INSERT INTO users_admin
               (fname, lname, email, password, role, profile_pic)
            VALUES (?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Note the type string: s = string, b = blob
    $stmt->bind_param("sssssb",
        $fname,
        $lname,
        $email,
        $hashed,
        $role,
        $pic_blob
    );
    // send_long_data for the blob (zero‑based index = 5)
    $stmt->send_long_data(5, $pic_blob);

    if ($stmt->execute()) {
        echo "Admin created successfully.";
    } else {
        die("Execute failed: " . $stmt->error);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// If GET, show a quick form
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Add Admin</title></head>
<body>
    <h1>Add Admin</h1>
    <form method="POST" enctype="multipart/form-data">
      <label>First Name: <input name="fname" required></label><br>
      <label>Last  Name: <input name="lname" required></label><br>
      <label>Email     : <input type="email" name="email" required></label><br>
      <label>Password  : <input type="password" name="password" required></label><br>
      <label>Picture   : <input type="file" name="profile_pic" accept="image/*" required></label><br>
      <button type="submit">Create Admin</button>
    </form>
</body>
</html>
