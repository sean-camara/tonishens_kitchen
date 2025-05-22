<?php
$host     = "localhost";
$user     = "root";
$password = "";
$dbname   = "users_db";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("SET time_zone = '+08:00'");
