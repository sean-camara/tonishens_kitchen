<?php
session_start();
include("connect.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Sanitize and retrieve POST data
$email = trim($_POST['email']);
$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

// Fetch current email & password from DB
$stmt = $conn->prepare("SELECT email, password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit();
}

if (!password_verify($current_password, $user['password'])) {
    echo "Incorrect current password.";
    exit();
}

if ($new_password !== $confirm_password) {
    echo "New passwords do not match.";
    exit();
}

// Hash new password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update only password if email field is empty or unchanged
if (empty($email) || $email === $user['email']) {
    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update_stmt->bind_param("si", $hashed_password, $user_id);
} else {
    // Update both email and password
    $update_stmt = $conn->prepare("UPDATE users SET email = ?, password = ? WHERE id = ?");
    $update_stmt->bind_param("ssi", $email, $hashed_password, $user_id);
}

if ($update_stmt->execute()) {
    $_SESSION['message'] = "Account updated successfully.";
    header("Location: profile.php");
    exit();
} else {
    echo "Failed to update account.";
}
?>
