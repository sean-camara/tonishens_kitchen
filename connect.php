<?php
// connect.php

// 1) Your usual connection code (adjust host/user/pass/dbname as needed)
$host     = "localhost";
$user     = "root";
$password = "";
$dbname   = "users_db";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2) Immediately force MySQL session to use Asia/Manila (UTC+8)
$conn->query("SET time_zone = '+08:00'");

// Now $conn is ready, and all NOW()/CURDATE() calls will follow Manila time.
