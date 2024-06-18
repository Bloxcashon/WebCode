<?php

require_once 'config.php';

session_start();
unset($_SESSION['user_token']);
session_destroy();
header("Location: index.php");


logout();
header("Location: index.php");
die();
?>