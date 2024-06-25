<?php
// check_auth.php

session_start();

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    echo json_encode(['authenticated' => true]);
} else {
    echo json_encode(['authenticated' => false]);
}