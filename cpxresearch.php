<?php
require_once 'config.php';
$conn = db_connect();

// Get user information from POST parameters
$unique_id = $_POST['unique_id'];
$username = $_POST['username'];
$email = $_POST['email'];

// Print the info to the console
echo "<script>console.log('User Information:', { unique_id: '$unique_id', username: '$username', email: '$email' });</script>";

// Generate a secure hash using the unique ID and app secure hash
$app_secure_hash = 'y3imiLOo2IuRyxMbLbLMJqYllA4cbnzU'; // Replace with your app secure hash
$secure_hash = md5($unique_id . '-' . $app_secure_hash);

// Create the iframe URL with necessary parameters
$iframe_url = "https://offers.cpx-research.com/index.php?app_id=23232&ext_user_id={$unique_id}&secure_hash={$secure_hash}&username={$username}&email={$email}&subid_1=&subid_2";

// Display iframe
echo "<iframe width='100%' frameBorder='0' height='2000px' src='{$iframe_url}'></iframe>";

?>