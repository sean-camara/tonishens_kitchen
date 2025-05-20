<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sign in</title>
    <link rel="stylesheet" href="sign-in-style.css" />
</head>
<body>
    <div class="container">
        <div class="box1">
            <img id="logo" src="images/logo.jpg" alt="logo" />
            <div class="box1-text">
                <h1>Welcome to Tonishen's <br /> Kitchen</h1>
                <p id="p-text">Where every bite feels like home.</p>

                <form action="login-process.php" method="POST" class="form">
                    <div class="input-box">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" required />
                    </div>

                    <div class="input-box">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" required />
                    </div>

                    <div class="input-box">
                        <label for="role">Role</label>
                        <select name="role" id="role" required>
                            <option value="">Select Role</option>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <!-- Show error if any -->
                   <?php
                    if (isset($_GET['error'])) {
                        echo "<p class='error-msg'>" . htmlspecialchars($_GET['error']) . "</p>";
                    }
                    ?>

                    <p class="signup-text">
                        Don't have an account yet?
                        <a href="sign-up.php">Sign up</a>
                    </p>

                    <button name="login" type="submit">Login</button>
                </form>
            </div>
        </div>
        <div class="box2">
            <img src="images/Rectangle 22.png" alt="background image" />
        </div>
    </div>
</body>
</html>
