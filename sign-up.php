<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="sign-up-style.css">
</head>
<body>
    <div class="container">
        <div class="box1">
            <img id="logo" src="images/logo.jpg" alt="logo">
            <div class="box1-text">
                <h1>Let's get started!</h1>
                <form action="process.php" method="POST" class="form">
                    <div class="input-box">
                        <label for="fname">First Name</label>
                        <input type="text" name="fname" id="fname" required>
                    </div>

                    <div class="input-box">
                        <label for="lname">Last Name</label>
                        <input type="text" name="lname" id="lname" required>
                    </div>

                    <div class="input-box">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" required>
                    </div>

                    <div class="input-box">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" required>
                    </div>

                    <p>Already have an account? <a href="sign-in.php">Sign in</a></p>

                    <button type="submit" name="signup">Sign Up</button>
                </form>

                <?php
                // Check for error in the URL
                if (isset($_GET['error']) && $_GET['error'] == 'email_exists') {
                    echo "<p class='error-message'>This email is already registered. Please use a different one.</p>";
                }
                ?>
            </div>
        </div>
        <div class="box2">
            <img src="images/Rectangle 22.png" alt="background image">
        </div>
    </div>
</body>
</html>