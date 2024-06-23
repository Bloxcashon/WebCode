<?php
require_once 'config.php';

if (isset($_SESSION['current_winner'])) {
    $winner = $_SESSION['current_winner'];
    unset($_SESSION['current_winner']);
    logError("Winner cleared: $winner");
} else {
    logError("No winner to clear");
}

echo "Winner cleared";