<?php
require_once 'config.php';
$conn = db_connect();

function generateTxnId($conn) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $prefix = '';
    if (isset($_POST['gmp_id']) && $_POST['gmp_id'] != 0) {
        $prefix = 'TXN-GMP#';
    } else {
        $prefix = 'TXN-PRIV#';
    }
    $txnId = $prefix;
    for ($i = 0; $i < 17; $i++) {
        $txnId .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    // Check if the generated TXNID already exists in the database
    $sql = "SELECT * FROM wstatus WHERE TXNID = '$txnId'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        // If the TXNID already exists, generate a new one
        return generateTxnId($conn);
    } else {
        return $txnId;
    }
}

if (isset($_POST['unique_id']) && isset($_POST['user_id']) && isset($_POST['game_id']) && isset($_POST['gmp_id']) && isset($_POST['amount'])) {
    $unique_id = $_POST['unique_id'];
    $user_id = $_POST['user_id'];
    $game_id = $_POST['game_id'];
    $gmp_id = $_POST['gmp_id'];
    $amount = $_POST['amount'];
    $status = 'pending';
    $txnId = generateTxnId($conn);

    $sql = "INSERT INTO wstatus (unique_id, user_id, game_id, gmp_id, amount, status, TXNID) VALUES ('$unique_id', '$user_id', '$game_id', '$gmp_id', '$amount', '$status', '$txnId')";
    mysqli_query($conn, $sql);
}

if (isset($_POST['unique_id']) && isset($_POST['user_id']) && isset($_POST['game_id']) && isset($_POST['price'])) {
    $unique_id = $_POST['unique_id'];
    $user_id = $_POST['user_id'];
    $game_id = $_POST['game_id'];
    $price = $_POST['price'];
    $status = 'pending';
    $txnId = generateTxnId($conn);

    $sql = "INSERT INTO wstatus (unique_id, user_id, game_id, gmp_id, amount, status, TXNID) VALUES ('$unique_id', '$user_id', '$game_id', '0', '$price', '$status', '$txnId')";
    mysqli_query($conn, $sql);
}
?>