<?php
require_once 'config.php';
$conn = db_connect();

if (isset($_POST['giveawayId'])) {
    $giveawayId = $_POST['giveawayId'];

    $sql = "SELECT u.roblox_username, u.roblox_avatar_url, g.points
            FROM giveaway g
            JOIN users u ON g.unique_id = u.unique_id
            WHERE g.giveaway_id = '$giveawayId'
            ORDER BY g.entry_time DESC
            LIMIT 5";

    $result = mysqli_query($conn, $sql);
    $recentEntries = '';

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $recentEntries .= '<button class="recent-entry-btn">
                                  <img src="' . $row['roblox_avatar_url'] . '" alt="Avatar">
                                  <span class="entry-username">' . $row['roblox_username'] . '</span>
                                  <span class="entry-captchas">' . $row['points'] . ' entry point(s)</span>
                              </button>';
        }
    } else {
        $recentEntries = '<p>No recent entries yet.</p>';
    }

    echo $recentEntries;
}
?>