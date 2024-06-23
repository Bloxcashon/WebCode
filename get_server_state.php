<?php
require_once 'config.php';
require_once 'upgiveaway.php';

$conn = db_connect();

// Get the current giveaway ID
$giveawayId = getGiveawayId($conn);

// Get the current server time
$serverTime = time() * 1000; // Convert to milliseconds to match JavaScript

// Calculate the time remaining in the current giveaway round
$currentHour = date('G');
$nextHour = ($currentHour + 1) % 24;
$nextRoundStart = strtotime("today $nextHour:00:00");
$timeRemaining = $nextRoundStart - time();

// Determine the current state based on the time remaining
if ($timeRemaining <= 120) { // Last 2 minutes
    $state = 'displaying_winner';
} elseif ($timeRemaining <= 300) { // Last 5 minutes
    $state = 'selecting_winner';
} else {
    $state = 'accepting_entries';
}

// Get the current winner if in the displaying_winner state
$currentWinner = '';
if ($state === 'displaying_winner') {
    $winnerQuery = "SELECT winner FROM gwinners WHERE giveaway_id = ? ORDER BY timestamp DESC LIMIT 1";
    $stmt = $conn->prepare($winnerQuery);
    $stmt->bind_param("i", $giveawayId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $currentWinner = $row['winner'];
    }
}

// Prepare the response
$response = [
    'state' => $state,
    'serverTime' => $serverTime,
    'timeRemaining' => $timeRemaining * 1000, // Convert to milliseconds
    'currentWinner' => $currentWinner,
    'giveawayId' => $giveawayId,
    'canEnter' => $timeRemaining > 300 // Only allow entries if more than 5 minutes remaining
];

// Send the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
mysqli_close($conn);