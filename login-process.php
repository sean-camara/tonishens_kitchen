<?php
session_start();
require 'connect.php'; // Connect to your database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role']; // Get role from form

    if ($role === 'admin') {
        // Check in users_admin table
        $stmt = $conn->prepare("SELECT id, fname, lname, email, password, role, profile_pic FROM users_admin WHERE email = ?");
    } elseif ($role === 'user') {
        // Check in users table
        $stmt = $conn->prepare("SELECT id, fname, lname, email, password, role, profile_pic FROM users WHERE email = ?");
    } else {
        header("Location: sign-in.php?error=Invalid role selected.");
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $fname, $lname, $db_email, $hashed_password, $db_role, $profile_pic);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            if ($role === $db_role) {
                $_SESSION['user_id'] = $id;
                $_SESSION['user_fname'] = $fname;
                $_SESSION['user_lname'] = $lname;
                $_SESSION['user_email'] = $db_email;
                $_SESSION['user_role'] = $db_role;
                $_SESSION['user_name'] = $fname . ' ' . $lname;
                $_SESSION['profile_pic'] = $profile_pic;

                if ($role === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: home.php");
                }
                exit();
            } else {
                header("Location: sign-in.php?error=Incorrect role selected.");
                exit();
            }
        } else {
            header("Location: sign-in.php?error=Invalid password. Please try again.");
            exit();
        }
    } else {
        header("Location: sign-in.php?error=No account found with that email.");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: sign-in.php");
    exit();
}
?>
