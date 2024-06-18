<?php
require_once 'config.php';
$conn = db_connect();

$sql = "SELECT COUNT(*) as num_entries FROM giveaway";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

echo $row['num_entries'];
?>