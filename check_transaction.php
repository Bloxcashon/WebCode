<?php
require_once 'config.php';
$conn = db_connect();

if (isset($_GET['txn_id'])) {
    $txn_id = $_GET['txn_id'];
    
    // Check wstatus table
    $sql = "SELECT w.*, u.roblox_username 
            FROM wstatus w 
            JOIN users u ON w.user_id = u.user_id 
            WHERE w.TXNID = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $txn_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $status = $row['status'];
        $roblox_username = $row['roblox_username'];
        
        if ($status === 'completed') {
            // Check fintxn table
            $fintxn_sql = "SELECT * FROM fintxn WHERE TXNID = ?";
            $fintxn_stmt = mysqli_prepare($conn, $fintxn_sql);
            mysqli_stmt_bind_param($fintxn_stmt, "s", $txn_id);
            mysqli_stmt_execute($fintxn_stmt);
            $fintxn_result = mysqli_stmt_get_result($fintxn_stmt);
            
            if (mysqli_num_rows($fintxn_result) > 0) {
                $fintxn_row = mysqli_fetch_assoc($fintxn_result);
                echo json_encode([
                    'status' => 'success',
                    'amount' => $fintxn_row['amount'],
                    'roblox_username' => $roblox_username
                ]);
                exit;
            }
        } elseif ($status === 'failed') {
            // Check errtxn table
            $errtxn_sql = "SELECT * FROM errtxn WHERE TXNID = ?";
            $errtxn_stmt = mysqli_prepare($conn, $errtxn_sql);
            mysqli_stmt_bind_param($errtxn_stmt, "s", $txn_id);
            mysqli_stmt_execute($errtxn_stmt);
            $errtxn_result = mysqli_stmt_get_result($errtxn_stmt);
            
            if (mysqli_num_rows($errtxn_result) > 0) {
                $errtxn_row = mysqli_fetch_assoc($errtxn_result);
                echo json_encode([
                    'status' => 'error',
                    'error_code' => $errtxn_row['error_code'],
                    'error_message' => $errtxn_row['error_message']
                ]);
                exit;
            }
        } else {
            // Transaction still pending
            echo json_encode(['status' => 'pending']);
            exit;
        }
    }
}

// Transaction not found
echo json_encode(['status' => 'not_found']);
?>