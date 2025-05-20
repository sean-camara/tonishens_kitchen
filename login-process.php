<?php
session_start();
include 'connect.php'; // Connect to your database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role']; // Get role from form

    // Prepare and execute query to get user info including role
    $stmt = $conn->prepare("SELECT id, fname, lname, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $fname, $lname, $db_email, $hashed_password, $db_role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // Check if selected role matches the database role
            if ($role === $db_role) {
                // ✅ Set all needed session variables
                $_SESSION['user_id'] = $id;
                $_SESSION['user_fname'] = $fname;
                $_SESSION['user_lname'] = $lname;
                $_SESSION['user_email'] = $db_email;
                $_SESSION['user_role'] = $db_role;
                $_SESSION['user_name'] = $fname . ' ' . $lname;

                // Redirect based on role
                if ($db_role === 'admin') {
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
