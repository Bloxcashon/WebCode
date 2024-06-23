<?php
require_once 'config.php';
$conn = db_connect();

$sql = "SELECT e.*, u.roblox_username, u.roblox_avatar_url 
        FROM earned e 
        JOIN users u ON e.unique_id = u.unique_id 
        ORDER BY e.time DESC LIMIT 4";
$result = mysqli_query($conn, $sql);

$activities = [];
while ($row = mysqli_fetch_assoc($result)) {
    $activities[] = [
        'username' => $row['roblox_username'],
        'avatar' => $row['roblox_avatar_url'],
        'type' => $row['type'],
        'url' => $row['url'],
        'earnings' => $row['earnings']
    ];
}

echo json_encode($activities);