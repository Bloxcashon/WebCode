<?php
require_once 'config.php';
require_once 'upgiveaway.php';
$conn = db_connect();

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

function sendWinnerNotification($winnerUniqueId, $giveawayId, $amount) {
    global $conn;
    $sql = "SELECT roblox_username FROM users WHERE unique_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $winnerUniqueId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $winnerUsername = $row['roblox_username'];

    $message = "event: winner\n";
    $message .= "data: " . json_encode(['username' => $winnerUsername, 'amount' => $amount]) . "\n\n";
    echo $message;
    flush();
}

while (true) {
    $currentGiveawayId = getGiveawayId($conn);
    $sql = "SELECT * FROM gwinners WHERE giveaway_id = ? AND notified = 0 ORDER BY time DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $currentGiveawayId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        sendWinnerNotification($row['unique_id'], $row['giveaway_id'], $row['amount']);
        
        // Mark as notified
        $updateSql = "UPDATE gwinners SET notified = 1 WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("i", $row['id']);
        $updateStmt->execute();
    }
    
    sleep(5);
}