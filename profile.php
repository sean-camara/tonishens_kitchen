<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit();
}

$user_name = $_SESSION['user_name']; // Get the user's name from the session

include("connect.php");

$user_id = $_SESSION['user_id'];

$query = "SELECT fname, lname, address, mobile, profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

$fname = $user_data['fname'];
$lname = $user_data['lname'];
$address = $user_data['address'];
$mobile = $user_data['mobile'];
$profile_pic = $user_data['profile_pic'] ?? 'images/default-avatar.png'; // fallback

$navbar_profile_pic = !empty($user_data['profile_pic']) ? $user_data['profile_pic'] : 'images/user.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="profile-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Playfair+Display&family=Poppins&display=swap" rel="stylesheet">
    <link rel="icon" id="tab-logo" type="image/png" href="images/Ellipse 2.png">
</head>
<body>
    <div class="nav-bar">
        <a href="home.php" style="text-decoration: none;"><div class="logo">
            <img id="logo-img" src="images/logo.jpg" alt="logo">
            <h1>Tonishen's Kitchen</h1>
        </div></a>

        <div class="nav-link">
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="home-menu.php">Menu</a></li>
                <li><a href="about.php">About</a></li>
            </ul>
        </div>

        <div class="icons">
            <a href=""><i id="cart" class="fa-solid fa-cart-shopping fa-3x"></i></a>
            <a href="profile.php"><img id="user" src="<?= $navbar_profile_pic ?>" alt="User image"></a>
        </div>
    </div>

    <div class="container">
        <div class="box1">
            <div class="myacc">
                <h3>My Account</h3>
            </div>
            <div class="myacc-btn">
                <a href="#"><button id="profile" class="btn">Profile</button></a>
            </div>
            <div class="myacc-btn">
                <a href="#"><button id="settings" class="btn">Settings</button></a>
            </div>
            <div class="myacc-btn">
                <a href="#"><button id="logout" class="btn">Logout</button></a>
            </div>
        </div>

        <div id="profile_con" class="box2">
            <div class="box2-con">
                <h2>Profile Settings</h2>
            </div>
            <div class="profile-settings">
                <form action="update-profile.php" method="POST" enctype="multipart/form-data">
                    <div class="profile-pic-section">
                        <label for="profile_pic">
                            <img id="profilePreview" src="<?= $profile_pic ?>" alt="Profile Picture" />
                        </label>

                        <button type="button" id="changePicBtn" title="Change Profile Picture">
                            <i class="fa-solid fa-camera"></i> Change
                        </button>

                        <input type="file" id="profile_pic" name="profile_pic" accept="image/*" hidden>
                    </div>

                    <div class="form-section">
                    <label for="fname">First Name</label>
                    <input type="text" name="fname" id="fname" value="<?= htmlspecialchars($fname) ?>" required />

                    <label for="lname">Last Name</label>
                    <input type="text" name="lname" id="lname" value="<?= htmlspecialchars($lname) ?>" required />

                    <label for="address">Address</label>
                    <input type="text" name="address" id="address" placeholder="Optional address field" value="<?= htmlspecialchars($address) ?>" />

                    <label for="number">Mobile Number</label>
                    <input type="text" name="number" id="number" placeholder="+63" value="<?= htmlspecialchars($mobile) ?>" />

                    <button class="save-changes" type="submit">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="settings_con" class="box2" style="display: none;">
            <div class="box2-con">
                <h2>Account Settings</h2>
            </div>
            <div class="profile-settings">
                <form action="update-account.php" method="POST">
        <div class="form-section">
          <label for="email">New Email</label>
          <input type="email" name="email" id="email" placeholder="Enter new email (leave blank if unchanged)" />

          <label for="current_password">Current Password</label>
          <input type="password" name="current_password" id="current_password" placeholder="Current password" required />

          <label for="new_password">New Password</label>
          <input type="password" name="new_password" id="new_password" placeholder="New password" required />

          <label for="confirm_password">Confirm New Password</label>
          <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" required />

          <button class="save-changes" type="submit">Update Account</button>
        </div>
      </form>
    </div>
        </div>

        <div id="logout_con" class="box2" style="display: none;">
            <div class="box2-con">
                <h2>Logout</h2>
            </div>
            <div class="logout-con">
                <p id="logout-text">Logout</p>
                <button id="logoutBtn" class="btn">Logout</button>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>