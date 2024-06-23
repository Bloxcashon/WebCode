<?php
// Get the postback parameters from the URL
$status = $_GET['status'];
$trans_id = $_GET['trans_id'];
$user_id = $_GET['user_id'];
$sub_id = $_GET['sub_id'];
$sub_id_2 = $_GET['sub_id_2'];
$amount_local = $_GET['amount_local'];
$amount_usd = $_GET['amount_usd'];
$offer_id = $_GET['offer_id'];
$secure_hash = $_GET['hash'];
$ip_click = $_GET['ip_click'];

// Verify the secure hash
$app_secure_hash = 'y3imiLOo2IuRyxMbLbLMJqYllA4cbnzU'; // Replace with your app secure hash
$secure_hash_check = md5($trans_id . '-' . $app_secure_hash);

if ($secure_hash != $secure_hash_check) {
    // Secure hash verification failed
    echo 'CPX Research postback secure hash verification failed';
    exit;
}

// Insert a new record in the offers table
require_once 'config.php';
$conn = db_connect();

// Insert a new record into the offers table with the postback data
$sql_offers = "INSERT INTO offers (unique_id, offer_id, trans_id, amount, status) VALUES (?, ?, ?, ?, ?)";
$stmt_offers = $conn->prepare($sql_offers);
$stmt_offers->bind_param("sssss", $user_id, $offer_id, $trans_id, $amount_local, $status);

// Assuming sub_id is the email and sub_id_2 is the username
$email = $sub_id;
$username = $sub_id_2;


if ($stmt_offers->execute()) {
    echo 'CPX Research postback successful. Inserted new record into offers table for User ID: ' . $user_id . ', Offer ID: ' . $offer_id . ', Transaction ID: ' . $trans_id . ', Amount: ' . $amount_local . ', Status: ' . $status . ', Email: ' . $email . ', Username: ' . $username;

    // Insert record into earned table for the user
    $sql_earned = "INSERT INTO earned (unique_id, earnings, type, url, time, date) VALUES (?, ?, 'survey', 'cpxresearch', NOW(), CURDATE())";
    $stmt_earned = $conn->prepare($sql_earned);
    $stmt_earned->bind_param("sd", $user_id, $amount_local);
    $stmt_earned->execute();
    $stmt_earned->close();

    // Update userpoints table for the user
    $sql_update_user = "UPDATE userpoints SET emethod = emethod + ?, total = total + ?, balance = total - withdrawn WHERE unique_id = ?";
    $stmt_update_user = $conn->prepare($sql_update_user);
    $stmt_update_user->bind_param("dds", $amount_local, $amount_local, $user_id);
    $stmt_update_user->execute();
    $stmt_update_user->close();

    // Get referrer's unique_id from the referrals table
    $sql_referrer = "SELECT referrer_id FROM referrals WHERE referred_id = ?";
    $stmt_referrer = $conn->prepare($sql_referrer);
    $stmt_referrer->bind_param("s", $user_id);
    $stmt_referrer->execute();
    $result_referrer = $stmt_referrer->get_result();

    if ($result_referrer->num_rows > 0) {
        $row = $result_referrer->fetch_assoc();
        $referrer_id = $row['referrer_id'];
        
        // Calculate referrer's earnings (10% of user's earnings)
        $referrer_earnings = $amount_local * 0.1;

        // Insert record into earned table for the referrer
        $sql_referrer_earned = "INSERT INTO earned (unique_id, earnings, type, url, time, date) VALUES (?, ?, 'referral', ?, NOW(), CURDATE())";
        $stmt_referrer_earned = $conn->prepare($sql_referrer_earned);
        $stmt_referrer_earned->bind_param("sds", $referrer_id, $referrer_earnings, $user_id);
        $stmt_referrer_earned->execute();
        $stmt_referrer_earned->close();

        // Update userpoints table for the referrer
        $sql_update_referrer = "UPDATE userpoints SET refbonus = refbonus + ?, total = total + ?, balance = total - withdrawn WHERE unique_id = ?";
        $stmt_update_referrer = $conn->prepare($sql_update_referrer);
        $stmt_update_referrer->bind_param("dds", $referrer_earnings, $referrer_earnings, $referrer_id);
        $stmt_update_referrer->execute();
        $stmt_update_referrer->close();

        // Insert record into refearn table
        $sql_refearn = "INSERT INTO refearn (referrer_id, referred_id, earnings, created_at) VALUES (?, ?, ?, NOW())";
        $stmt_refearn = $conn->prepare($sql_refearn);
        $stmt_refearn->bind_param("ssd", $referrer_id, $user_id, $referrer_earnings);
        $stmt_refearn->execute();
        $stmt_refearn->close();
    }

    $stmt_referrer->close();
} else {
    echo 'CPX Research postback failed. Error inserting into offers table: ' . $stmt_offers->error;
}

$stmt_offers->close();
$conn->close();
?>