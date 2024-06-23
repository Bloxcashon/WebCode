<?php
require_once 'config.php';


logError("Current session data: " . print_r($_SESSION, true));

if (isset($_SESSION['current_winner'])) {
    echo $_SESSION['current_winner'];
} else {
    logError("No current winner set in session");
    echo '';
}