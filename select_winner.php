<?php
require_once 'config.php';
require_once 'upgiveaway.php';
$conn = db_connect();

$giveawayId = getGiveawayId($conn);
$winner = handleWinnerSelection($conn, $giveawayId);

if ($winner) {
    echo json_encode(['status' => 'success', 'winner' => $winner]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to select winner']);
}