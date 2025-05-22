<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin-user-account.php');
    exit;
}

if (!isset($_POST['save_admin'])) {
    header('Location: ../admin-user-account.php');
    exit;
}

$id       = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int)$_POST['id'] : 0;
$fname    = trim($_POST['fname']  ?? '');
$lname    = trim($_POST['lname']  ?? '');
$email    = trim($_POST['email']  ?? '');
$password = $_POST['password']    ?? '';
$role     = 'admin';

if (!$fname || !$lname || !$email) {
    die('First name, last name and email are required.');
}

$pic_blob = null;
if (!empty($_FILES['profile_pic']['tmp_name'])) {
    $tmp = $_FILES['profile_pic']['tmp_name'];
    $pic_blob = file_get_contents($tmp);
}

if ($id === 0) {
    if (!$password) {
        die('Password is required for a new admin.');
    }
    if ($pic_blob === null) {
        die('Profile picture is required for a new admin.');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users_admin (fname, lname, email, password, role, profile_pic)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param('sssssb', $fname, $lname, $email, $hash, $role, $pic_blob);
    $stmt->send_long_data(5, $pic_blob);

    if (!$stmt->execute()) {
        die('Execute failed: ' . $stmt->error);
    }

    $stmt->close();
    header('Location: ../admin-user-account.php');
    exit;
}

$updates = [];
$types   = '';
$params  = [];

$updates[] = 'fname = ?'; $types .= 's'; $params[] = $fname;
$updates[] = 'lname = ?'; $types .= 's'; $params[] = $lname;
$updates[] = 'email = ?'; $types .= 's'; $params[] = $email;

if ($password !== '') {
    $updates[] = 'password = ?';
    $types   .= 's';
    $params[] = password_hash($password, PASSWORD_DEFAULT);
}

if ($pic_blob !== null) {
    $updates[] = 'profile_pic = ?';
    $types   .= 'b';
    $params[] = $pic_blob;
}

$updates[] = 'role = ?'; $types .= 's'; $params[] = $role;

$sql = "UPDATE users_admin SET " . implode(', ', $updates) . " WHERE id = ?";
$types  .= 'i';
$params[] = $id;

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}

$stmt->bind_param($types, ...$params);

if ($pic_blob !== null) {
    $pos = strpos($types, 'b');
    if ($pos !== false) {
        $stmt->send_long_data($pos, $pic_blob);
    }
}

if (!$stmt->execute()) {
    die('Execute failed: ' . $stmt->error);
}

$stmt->close();
header('Location: ../admin-user-account.php');
exit;
