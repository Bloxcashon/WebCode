<?php
require_once 'config.php';
$conn = db_connect();

function getGiveawayId($conn) {
    $utcHour = gmdate('H');
    $currentGiveawayId = get_current_giveaway_id($conn, $utcHour);
    if ($currentGiveawayId) {
        return $currentGiveawayId;
    }

    $giveawayId = 'GID#' . substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, rand(2, 13));
    $sql = "INSERT INTO giveaway_ids (giveaway_id, hour) VALUES ('$giveawayId', '$utcHour')";
    mysqli_query($conn, $sql);
    return $giveawayId;
}

function get_current_giveaway_id($conn, $hour) {
    $sql = "SELECT giveaway_id FROM giveaway_ids WHERE hour = $hour";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['giveaway_id'];
    }
    return false;
}

function createGiveawayEntry($conn, $uniqueId, $giveawayId) {
    // First, check if the user has already entered the giveaway
    $sql = "SELECT COUNT(*) as count FROM `giveaway` WHERE giveaway_id = '$giveawayId' AND unique_id = '$uniqueId'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if ($row['count'] > 0) {
        // User has already entered the giveaway
        return false;
    } else {
        // User has not entered the giveaway, create a new entry
        $sql = "INSERT INTO `giveaway` (giveaway_id, unique_id, entry_time) VALUES ('$giveawayId', '$uniqueId', NOW())";
        mysqli_query($conn, $sql);
        return true;
    }
}

function getRandomWinner($conn, $giveawayId) {
    // Get all entries for the current giveaway
    $sql = "SELECT unique_id, points FROM giveaway WHERE giveaway_id = '$giveawayId'";
    $result = mysqli_query($conn, $sql);

    // Create an array of entries, where each entry is repeated based on its points
    $entries = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $uniqueId = $row['unique_id'];
        $points = $row['points'];
        for ($i = 0; $i < $points; $i++) {
            $entries[] = $uniqueId;
        }
    }

    // Shuffle the entries array
    shuffle($entries);

    // Select a random winner from the shuffled array
    $winnerUniqueId = $entries[array_rand($entries)];

    return $winnerUniqueId;
}

function handleWinnerSelection($conn, $giveawayId) {
    // Get the winner's unique_id
    $winnerUniqueId = getRandomWinner($conn, $giveawayId);

    // Get the giveaway details
    $sql = "SELECT giveaway_price FROM giveaway WHERE giveaway_id = '$giveawayId' LIMIT 1";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $giveawayPrice = $row['giveaway_price'];

    // Record the winner's details in the gwinners table
    $sql = "INSERT INTO gwinners (unique_id, giveaway_id, amount, time) VALUES ('$winnerUniqueId', '$giveawayId', '$giveawayPrice', NOW())";
    mysqli_query($conn, $sql);

}

function getEntriesCount($conn, $giveawayId) {
    $sql = "SELECT COUNT(*) as entries_count FROM `giveaway` WHERE giveaway_id = '$giveawayId'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['entries_count'];
}

if (isset($_POST['enterGiveaway'])) {
    $uniqueId = $_POST['uniqueId'];
    $giveawayId = $_SESSION['giveawayId']; // Use the giveaway ID from the session

    $entrySuccess = createGiveawayEntry($conn, $uniqueId, $giveawayId);

    if ($entrySuccess) {
        echo 'Entry successful!';
    } else {
        echo 'You have already entered this giveaway.';
    }
}
