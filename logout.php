<?php
session_start();

session_unset(); 

session_destroy();

header("Location: sign-in.php");
exit();