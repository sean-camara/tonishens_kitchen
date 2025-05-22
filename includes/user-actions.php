<?php
// includes/admin-action.php
ini_set('display_errors',1);
error_reporting(E_ALL);

require '../connect.php';

// ADD or UPDATE an admin
if (isset($_POST['save_admin'])) {
    $id       = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $fname    = $_POST['fname'] ?? '';
    $lname    = $_POST['lname'] ?? '';
    $email    = $_POST['email'] ?? '';
    $role     = 'admin';  // always admin here
    $password = !empty($_POST['password'])
              ? password_hash($_POST['password'], PASSWORD_DEFAULT)
              : null;
    $pic_blob = !empty($_FILES['profile_pic']['tmp_name'])
              ? file_get_contents($_FILES['profile_pic']['tmp_name'])
              : null;

    if ($id > 0) {
        // UPDATE existing admin
        $sql    = "UPDATE users_admin
                   SET fname=?, lname=?, email=?";
        $types  = "sss";
        $params = [$fname, $lname, $email];

        if ($password !== null) {
            $sql   .= ", password=?";
            $types .= "s";
            $params[] = $password;
        }
        if ($pic_blob !== null) {
            $sql   .= ", profile_pic=?";
            $types .= "b";
            $params[] = $pic_blob;
        }

        $sql .= " WHERE id=?";
        $types .= "i";
        $params[] = $id;

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        if ($pic_blob !== null) {
            // blob is parameter index of first 'b'
            $idx = strpos($types, 'b');
            $stmt->send_long_data($idx, $pic_blob);
        }
        $stmt->execute();
        $stmt->close();

    } else {
        // INSERT new admin — password and pic required
        if ($password === null || $pic_blob === null) {
            die("Password and profile picture are required for new admins.");
        }
        $sql = "INSERT INTO users_admin
                (fname,lname,email,password,profile_pic,role)
                VALUES (?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssbs", 
            $fname, $lname, $email, $password, $pic_blob, $role
        );
        // 4th param index for blob = 4 (0-based)
        $stmt->send_long_data(4, $pic_blob);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: ../admin-user-account.php");
    exit();
}

// DELETE admin
if (isset($_POST['delete_user'])) {
    $id = intval($_POST['delete_id'] ?? 0);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM users_admin WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: ../admin-user-account.php");
    exit();
}
?>
