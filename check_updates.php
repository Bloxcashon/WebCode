<?php
require_once 'config.php';
$conn = db_connect();

$unique_id = $_GET['unique_id'];

// Check for new records in fintxn table
$fintxn_sql = "SELECT * FROM fintxn WHERE TXNID IN (SELECT TXNID FROM wstatus WHERE unique_id = '$unique_id' AND status = 'pending')";
$fintxn_result = mysqli_query($conn, $fintxn_sql);
$fintxn_rows = mysqli_num_rows($fintxn_result);

// Check for new records in errtxn table
$errtxn_sql = "SELECT * FROM errtxn WHERE TXNID IN (SELECT TXNID FROM wstatus WHERE unique_id = '$unique_id' AND status = 'pending')";
$errtxn_result = mysqli_query($conn, $errtxn_sql);
$errtxn_rows = mysqli_num_rows($errtxn_result);

// Return JSON response
$response = array();
if ($fintxn_rows > 0) {
    while ($fintxn_row = mysqli_fetch_assoc($fintxn_result)) {
        $response[] = array(
            'type' => 'fintxn',
            'TXNID' => $fintxn_row['TXNID'],
            'amount' => $fintxn_row['amount']
        );
    }
} elseif ($errtxn_rows > 0) {
    while ($errtxn_row = mysqli_fetch_assoc($errtxn_result)) {
        $response[] = array(
            'type' => 'errtxn',
            'TXNID' => $errtxn_row['TXNID'],
            'error_code' => $errtxn_row['error_code'],
            'error_message' => $errtxn_row['error_message']
        );
    }
}

if ($response) {
    foreach ($response as $update) {
        if ($update['type'] === 'fintxn') {
            // Check if TXNID exists in wstatus table
            $txn_id = $update['TXNID'];
            $wstatus_query = "SELECT unique_id, user_id FROM wstatus WHERE TXNID = '$txn_id'";
            $wstatus_result = mysqli_query($conn, $wstatus_query);
            if (mysqli_num_rows($wstatus_result) > 0) {
                $wstatus_row = mysqli_fetch_assoc($wstatus_result);
                $unique_id = $wstatus_row['unique_id'];
                $user_id = $wstatus_row['user_id'];
                $fintxn_update_query = "UPDATE fintxn SET unique_id = '$unique_id', user_id = '$user_id' WHERE TXNID = '$txn_id'";
                mysqli_query($conn, $fintxn_update_query);
                echo "Success! You have received ". $update['amount']. " R$. Kindly allow 3-5 days for it to reflect on your account. You can confirm the payout via this link https://www.roblox.com/transactions";
                // Delete corresponding row in wstatus table
                $wstatus_delete_query = "DELETE FROM wstatus WHERE TXNID = '$txn_id'";
                mysqli_query($conn, $wstatus_delete_query);
            } else {
                echo "TXNID $txn_id not found in wstatus table";
            }
        } elseif ($update['type'] === 'errtxn') {
            // Check if TXNID exists in wstatus table
            $txn_id = $update['TXNID'];
            $wstatus_query = "SELECT unique_id, user_id FROM wstatus WHERE TXNID = '$txn_id'";
            $wstatus_result = mysqli_query($conn, $wstatus_query);
            if (mysqli_num_rows($wstatus_result) > 0) {
                $wstatus_row = mysqli_fetch_assoc($wstatus_result);
                $unique_id = $wstatus_row['unique_id'];
                $user_id = $wstatus_row['user_id'];
                $errtxn_update_query = "UPDATE errtxn SET unique_id = '$unique_id', user_id = '$user_id' WHERE TXNID = '$txn_id'";
                mysqli_query($conn, $errtxn_update_query);
                echo "Error ". $update['error_code']. ": Your withdrawal could not be completed ". $update['error_message'];
                // Delete corresponding row in wstatus table
                $wstatus_delete_query = "DELETE FROM wstatus WHERE TXNID = '$txn_id'";
                mysqli_query($conn, $wstatus_delete_query);
            } else {
                echo "TXNID $txn_id not found in wstatus table";
            }
        }
    }
}

echo json_encode($response);
?>