<?php
require_once 'config.php';
require_once 'main.php';
$conn = db_connect();

$bonus_link = $_GET['bonus_link'];
$unique_id = $_GET['unique_id'];
$referrer_id = isset($_GET['referrer_id']) ? $_GET['referrer_id'] : null;

// Check if the user has already clicked on the same bonus link
$sql = "SELECT * FROM earned WHERE unique_id = ? AND type = 'bonus_link' AND url = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("ss", $unique_id, $bonus_link);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// ... (existing code)

if ($result->num_rows > 0) {
    // User has already clicked on the same bonus link, do nothing
} else {
    // Insert new record into the earned table
    $earnings = 0.5;
    $type = 'bonus_link';
    $url = $bonus_link;
    $time = date('H:i:s');
    $date = date('Y-m-d');

    $sql = "INSERT INTO earned (unique_id, earnings, type, url, time, date) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("sdsssss", $unique_id, $earnings, $type, $url, $time, $date);
    $stmt->execute();
    if (!$stmt->affected_rows) {
        die("Error executing statement: " . $stmt->error);
    }
    $stmt->close();

    // Check if the user has a referrer
    if ($referrer_id !== null && $referrer_id !== '') {
        // Calculate referral earnings (10% of user's earnings)
        $referral_earnings = $earnings * 0.1;

        // Update the referrer's points in the userpoints table
        $sql = "UPDATE userpoints SET refbonus = refbonus + ?, total = emethod + sclink + refbonus + codes, balance = total - withdrawn WHERE unique_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("ds", $referral_earnings, $referrer_id);
        $stmt->execute();
        if (!$stmt->affected_rows) {
            die("Error executing statement: " . $stmt->error);
        }
        $stmt->close();

        // Insert new record into the refearn table
        $sql = "INSERT INTO refearn (referrer_id, referred_id, earnings, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("ssd", $referrer_id, $unique_id, $referral_earnings);
        $stmt->execute();
        if (!$stmt->affected_rows) {
            die("Error executing statement: " . $stmt->error);
        }
        $stmt->close();

        // Insert new record into the earned table for the referrer
        $referrer_type = 'referral';
        $referrer_url = $unique_id;
        $sql = "INSERT INTO earned (unique_id, earnings, type, url, time, date) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("sdsssss", $referrer_id, $referral_earnings, $referrer_type, $referrer_url, $time, $date);
        $stmt->execute();
        if (!$stmt->affected_rows) {
            die("Error executing statement: " . $stmt->error);
        }
        $stmt->close();
    }

    // Update the user's points in the userpoints table
    if ($type == 'bonus_link') {
        $sql = "UPDATE userpoints SET sclink = sclink + ?, total = emethod + sclink + refbonus + codes, balance = total - withdrawn WHERE unique_id = ?";
    } elseif ($type == 'referral') {
        $sql = "UPDATE userpoints SET refbonus = refbonus + ?, total = emethod + sclink + refbonus + codes, balance = total - withdrawn WHERE unique_id = ?";
    } elseif ($type == 'promocode') {
        $sql = "UPDATE userpoints SET codes = codes + ?, total = emethod + sclink + refbonus + codes, balance = total - withdrawn WHERE unique_id = ?";
    }
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("ds", $earnings, $unique_id);
    $stmt->execute();
    if (!$stmt->affected_rows) {
        die("Error executing statement: " . $stmt->error);
    }
    $stmt->close();
}

$conn->close();
?>