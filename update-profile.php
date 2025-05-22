<?php
session_start();
require "connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Sanitize text fields
$fname   = mysqli_real_escape_string($conn, $_POST['fname']);
$lname   = mysqli_real_escape_string($conn, $_POST['lname']);
$address = mysqli_real_escape_string($conn, $_POST['address']);
$mobile  = mysqli_real_escape_string($conn, $_POST['number']);

// Check for an uploaded file
$hasImage = isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK;

if ($hasImage) {
    // Read binary data
    $blob = file_get_contents($_FILES['profile_pic']['tmp_name']);

    // Update including BLOB
    $sql = "UPDATE users
               SET fname       = ?,
                   lname       = ?,
                   address     = ?,
                   mobile      = ?,
                   profile_pic = ?
             WHERE id = ?";
    $stmt = $conn->prepare($sql);

    // Types: s=string, b=blob, i=int
    // Order of params: fname, lname, address, mobile, blob, user_id
    $stmt->bind_param("ssssbi", $fname, $lname, $address, $mobile, $blob, $user_id);

    // Send the blob in chunks
    $stmt->send_long_data(4, $blob);

} else {
    // Update text fields only
    $sql = "UPDATE users
               SET fname   = ?,
                   lname   = ?,
                   address = ?,
                   mobile  = ?
             WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $fname, $lname, $address, $mobile, $user_id);
}

if ($stmt->execute()) {
    echo "<script>
            alert('Profile updated successfully!');
            window.location.href = 'profile.php';
          </script>";
} else {
    echo "Error updating profile: " . htmlspecialchars($stmt->error);
}

$stmt->close();
$conn->close();
?>
