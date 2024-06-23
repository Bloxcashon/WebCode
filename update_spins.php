<?php
require_once 'config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_token'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$conn = db_connect();

$sql = "SELECT unique_id FROM users WHERE token = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
    exit;
}
$stmt->bind_param('s', $_SESSION['user_token']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(['error' => 'User not found']);
    exit;
}

$unique_id = $user['unique_id'];
$week_start = date('Y-m-d', strtotime('last sunday midnight'));

// Get current spins and earnings
$sql = "SELECT w.number, COALESCE(SUM(u.emethod + u.refbonus + u.sclink), 0) AS weekly_earnings 
        FROM wheelspins w 
        LEFT JOIN userpoints u ON w.unique_id = u.unique_id AND u.created_at >= ?
        WHERE w.unique_id = ? AND w.week_start = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
    exit;
}
$stmt->bind_param('sss', $week_start, $unique_id, $week_start);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$spins_used = $row['number'] ?? 0;
$weekly_earnings = $row['weekly_earnings'] ?? 0;
$free_spins = floor($weekly_earnings / 50);
$spins_left = max(0, $free_spins - $spins_used);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'spin') {
    if ($spins_left > 0) {
        // Update spins count
        $sql = "UPDATE wheelspins SET number = number + 1 WHERE unique_id = ? AND week_start = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(['error' => 'Database error: ' . $conn->error]);
            exit;
        }
        $stmt->bind_param('ss', $unique_id, $week_start);
        $stmt->execute();
        
        $spins_left--;
        echo json_encode(['spinsLeft' => $spins_left]);
    } else {
        echo json_encode(['error' => 'No spins left']);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
