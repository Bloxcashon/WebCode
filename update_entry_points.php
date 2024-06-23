<?php
require_once 'config.php';
require_once 'upgiveaway.php';
require_once 'functions.php'; 

$conn = db_connect();

$giveawayId = $_POST['giveawayId'];
$uniqueId = $_POST['uniqueId'];

$entryPoints = getUpdatedEntryPoints($conn, $uniqueId, $giveawayId);

echo $entryPoints;
?>