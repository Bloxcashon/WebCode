<?php
require_once 'config.php';
$conn = db_connect();

if (!isset($_SESSION['user_token'])) {
    header("Location: index.php");
    die();
}

$sql = "SELECT * FROM users WHERE token ='{$_SESSION['user_token']}'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    $userinfo = mysqli_fetch_assoc($result);
    $user_id = $userinfo['user_id'];
}

$url = "https://games.roblox.com/v2/users/$user_id/games?accessFilter=2&limit=10&sortOrder=Asc";
$response = file_get_contents($url);
$games = json_decode($response, true);

$thumbnails = array();
foreach ($games['data'] as $game) {
    $thumbnailUrl = "https://thumbnails.roblox.com/v1/games/multiget/thumbnails?universeIds={$game['id']}&countPerUniverse=1&defaults=true&size=768x432&format=Png&isCircular=false";
    $ch = curl_init($thumbnailUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $thumbnailResponse = curl_exec($ch);
    curl_close($ch);
    $thumbnailData = json_decode($thumbnailResponse, true);
    $thumbnails[$game['id']] = $thumbnailData['data'][0]['thumbnails'][0]['imageUrl'];
}

$gamesWithThumbnails = array();
foreach ($games['data'] as $game) {
    $game['thumbnailUrl'] = $thumbnails[$game['id']];
    $gamesWithThumbnails[] = $game;
}

ob_clean();
header('Content-Type: application/json');
echo json_encode(array('data' => $gamesWithThumbnails));
ob_flush();