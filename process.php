<?php

echo "✅ process.php is running!<br>";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "✅ Form was submitted via POST!<br>";

    echo "First Name: " . $_POST['fname'] . "<br>";
    echo "Last Name: " . $_POST['lname'] . "<br>";
    echo "Email: " . $_POST['email'] . "<br>";
    echo "Password: " . $_POST['password'] . "<br>";
} else {
    echo "❌ This page was not accessed through the form.";
}

include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get data from the form
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if email format is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<p>Invalid email format. Please try again.</p>";
        exit();
    }

    // ✅ Check if the email already exists in the users table
    $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();

    if ($check_email->num_rows > 0) {
        // Email is already registered
        header("Location: sign-up.php?error=email_exists");
        exit();
    }

    $check_email->close();

    // ✅ Hash the password to keep it safe
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // ✅ Insert the new user data into the users table
    $sql = "INSERT INTO users (fname, lname, email, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $fname, $lname, $email, $hashed_password);

    if ($stmt->execute()) {
        // Redirect to sign-in page with success message
        header("Location: sign-in.php?success=1");
        exit();
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<p>No data submitted.</p>";
}
?>
