<?php
require_once 'vendor/autoload.php';
session_start();

// init configuration
$clientID = '282378517494-298s3vdrcqirbv7v0v81f828je15ru7i.apps.googleusercontent.com';
$clientSecret = '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';
$redirectUri = 'username.php';

// create Client Request to access Google API
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");



// Database connection function
function db_connect() {
    $hostname = "localhost";
    $username = "root";
    $password = "";
    $database = "bc-user-data";

    ini_set('allow_url_fopen', true);

    $conn = mysqli_connect($hostname, $username, $password, $database);
    return $conn;
}
function logError($message) {
    $logFile = 'winnererror.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}
$admin_username = getenv('ADMIN_USERNAME') ?: 'admin';
$admin_password = getenv('ADMIN_PASSWORD') ?: 'password';

error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);


?>