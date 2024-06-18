<?php
require_once 'config.php';
$conn = db_connect();

if (isset($_POST['giveawayId'])) {

    // Fetch recent winners from the gwinners table
    $sql = "SELECT u.roblox_username, u.roblox_avatar_url, gw.amount
            FROM gwinners gw
            JOIN users u ON gw.unique_id = u.unique_id
            ORDER BY gw.time DESC
            LIMIT 5";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $html = '';
        while ($row = mysqli_fetch_assoc($result)) {
            $username = $row['roblox_username'];
            $avatarUrl = $row['roblox_avatar_url'];
            $amount = $row['amount'];
            $html .= '<button class="recent-entry-btn">
                        <img src="' . $avatarUrl . '" alt="Avatar">
                        <span class="entry-username">' . $username . '</span>
                        <span class="entry-captchas">Won ' . $amount . ' R$</span>
                      </button>';
        }
        echo $html;
    } else {
        echo '<p>No recent winners yet.</p>';
    }
}
?>