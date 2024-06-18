<?php
require_once 'config.php';
$conn = db_connect();

// Set appropriate headers for SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

function sendWinnerNotification($winnerUniqueId, $giveawayId, $amount) {
    global $conn;

    // Get the winner's Roblox username
    $sql = "SELECT roblox_username FROM users WHERE unique_id = '$winnerUniqueId'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $winnerUsername = $row['roblox_username'];

    // Send the winner notification
    $message = "event: winner\ndata: {\"username\": \"$winnerUsername\", \"amount\": $amount}\n\n";
    echo $message;
    flush();
}

// Keep the script running and listen for winner notifications
while (true) {
    // You can add some logic here to check for new winner notifications
    // and call the sendWinnerNotification function when needed

    // Example: Sleep for 5 seconds and then send a dummy notification
    sleep(5);
    sendWinnerNotification('unique_id_123', 'GID#123', 2000);
}