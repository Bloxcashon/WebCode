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

    // Clear old entries
    $sql = "DELETE FROM giveaway WHERE giveaway_id != '$giveawayId'";
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
    $sql = "SELECT unique_id, points FROM giveaway WHERE giveaway_id = '$giveawayId'";
    $result = mysqli_query($conn, $sql);

    $entries = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $uniqueId = $row['unique_id'];
        $points = $row['points'];
        for ($i = 0; $i < $points; $i++) {
            $entries[] = $uniqueId;
        }
    }

    shuffle($entries);

    $winnerUniqueId = $entries[array_rand($entries)];

    return $winnerUniqueId;
}
function getTotalPoints($conn, $giveawayId) {
    $sql = "SELECT SUM(points) AS total_points FROM giveaway WHERE giveaway_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $giveawayId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total_points'] ? $row['total_points'] : 0;
}

function handleWinnerSelection($conn, $giveawayId) {
    $lockKey = "winner_selection_lock_" . $giveawayId;
    
    // Try to acquire a lock
    $gotLock = $conn->query("SELECT GET_LOCK('$lockKey', 10) as lock_result")->fetch_assoc()['lock_result'];
    
    if (!$gotLock) {
        logError("Failed to acquire lock for winner selection. Giveaway ID: $giveawayId");
        return false;
    }
    
    // Check if a winner has already been selected
    $checkWinnerSql = "SELECT COUNT(*) as winner_count FROM gwinners WHERE giveaway_id = ?";
    $checkWinnerStmt = $conn->prepare($checkWinnerSql);
    $checkWinnerStmt->bind_param("s", $giveawayId);
    $checkWinnerStmt->execute();
    $winnerCount = $checkWinnerStmt->get_result()->fetch_assoc()['winner_count'];
    
    if ($winnerCount > 0) {
        $conn->query("SELECT RELEASE_LOCK('$lockKey')");
        return false; // Winner already selected
    }

    logError("Starting winner selection for giveaway ID: $giveawayId");
    
    $winnerUniqueId = getRandomWinner($conn, $giveawayId);
    if (!$winnerUniqueId) {
        logError("Failed to get random winner for giveaway ID: $giveawayId");
        return false;
    }
    logError("Random winner selected: $winnerUniqueId");

    // Get the giveaway details
    $sql = "SELECT giveaway_price FROM giveaway WHERE giveaway_id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $giveawayId);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        logError("Failed to get giveaway details: " . mysqli_error($conn));
        return false;
    }
    $row = $result->fetch_assoc();
    $giveawayPrice = $row['giveaway_price'];

    // Record the winner's details in the gwinners table
    $sql = "INSERT INTO gwinners (unique_id, giveaway_id, amount, time) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $winnerUniqueId, $giveawayId, $giveawayPrice);
    if (!$stmt->execute()) {
        logError("Failed to insert winner into gwinners table: " . mysqli_error($conn));
        return false;
    }
    logError("Winner recorded in gwinners table");

    // Get the winner's username
    $sql = "SELECT roblox_username FROM users WHERE unique_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $winnerUniqueId);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        logError("Failed to get winner's username: " . mysqli_error($conn));
        return false;
    }
    $row = $result->fetch_assoc();
    $winnerUsername = $row['roblox_username'];

    // Set the current winner in the session
    $_SESSION['current_winner'] = $winnerUsername;

    logError("Winner selection complete. Winner: $winnerUsername");
    $conn->query("SELECT RELEASE_LOCK('$lockKey')");
    return $winnerUsername;
}

function getEntriesCount($conn, $giveawayId) {
    $sql = "SELECT COUNT(*) as entries_count FROM `giveaway` WHERE giveaway_id = '$giveawayId'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['entries_count'];
}
if (isset($_POST['getTotalPoints']) && isset($_POST['giveawayId'])) {
    $giveawayId = $_POST['giveawayId'];
    $totalPoints = getTotalPoints($conn, $giveawayId);
    echo $totalPoints;
    exit;
}

if (isset($_POST['enterGiveaway']) && isset($_POST['uniqueId']) && isset($_POST['giveawayId']) && isset($_POST['rank'])) {
    $uniqueId = $_POST['uniqueId'];
    $giveawayId = $_POST['giveawayId'];
    $rank = $_POST['rank'];

    // Check if the giveaway ID is current
    $currentGiveawayId = getGiveawayId($conn);
    if ($giveawayId !== $currentGiveawayId) {
        echo json_encode(['status' => 'error', 'message' => 'Please refresh the page first to join the new giveaway.']);
        exit;
    }

    // Check if entries are still being accepted
    $currentTime = time();
    $nextHour = (date('G') + 1) % 24;
    $nextRoundStart = strtotime("today $nextHour:00:00");
    $timeRemaining = $nextRoundStart - $currentTime;

    if ($timeRemaining <= 300) { // 5 minutes or less remaining
        echo json_encode(['status' => 'error', 'message' => 'Entries are no longer being accepted for this round.']);
        exit;
    }

    // Check if the user has already entered
    $checkSql = "SELECT * FROM giveaway WHERE unique_id = ? AND giveaway_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ss", $uniqueId, $giveawayId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'already_entered', 'message' => 'You have already entered this giveaway.']);
    } else {
        // Determine additional entry points based on rank
        $additionalPoints = 0;
        switch ($rank) {
            case 'Coin Collector':
                $additionalPoints = 0;
                break;
            case 'robux-rookie':
                $additionalPoints = 2;
                break;
            case 'robux-ranger':
                $additionalPoints = 4;
                break;
            case 'robux-renegade':
                $additionalPoints = 8;
                break;
            case 'robux-royalty':
                $additionalPoints = 10;
                break;
            case 'robux-legend':
                $additionalPoints = 12;
                break;
        }

        // Insert the entry with additional points
        $insertSql = "INSERT INTO giveaway (unique_id, giveaway_id, points) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $totalPoints = 1 + $additionalPoints; // 1 for the basic entry + additional points
        $insertStmt->bind_param("ssi", $uniqueId, $giveawayId, $totalPoints);
        
        if ($insertStmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => "You received $totalPoints entry points (including $additionalPoints bonus points for your rank)."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to enter the giveaway.']);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>