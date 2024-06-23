<?php
require_once 'config.php';


$conn = db_connect();
$unique_id = $_POST['unique_id'];
$giveawayId = $_POST['giveawayId'];

$sql = "SELECT points FROM giveaway WHERE unique_id = ? AND giveaway_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $unique_id, $giveawayId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo $row['points'];
} else {
    echo "0";
}

$conn->close();
?>