<?php
session_start();
include("connect.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Sanitize and collect form data
$fname = mysqli_real_escape_string($conn, $_POST['fname']);
$lname = mysqli_real_escape_string($conn, $_POST['lname']);
$address = mysqli_real_escape_string($conn, $_POST['address']);
$mobile = mysqli_real_escape_string($conn, $_POST['number']);

$profile_pic_path = null;

// Check if a new profile picture was uploaded
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
    $file_tmp = $_FILES['profile_pic']['tmp_name'];
    $file_name = uniqid() . "_" . basename($_FILES['profile_pic']['name']);
    $upload_path = "images/" . $file_name;

    if (move_uploaded_file($file_tmp, $upload_path)) {
        $profile_pic_path = $upload_path;
    }
}

// Update query
if ($profile_pic_path) {
    $sql = "UPDATE users SET fname=?, lname=?, address=?, mobile=?, profile_pic=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $fname, $lname, $address, $mobile, $profile_pic_path, $user_id);
} else {
    $sql = "UPDATE users SET fname=?, lname=?, address=?, mobile=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $fname, $lname, $address, $mobile, $user_id);
}

if ($stmt->execute()) {
    echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
