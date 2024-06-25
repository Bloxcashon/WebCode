<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();

header('Content-Type: application/json');

echo json_encode(['success' => true, 'message' => 'Test response']);

$output = ob_get_clean();

if (strlen($output) > 0) {
    error_log("Unexpected output before JSON: " . $output);
    echo json_encode(['success' => false, 'message' => 'Unexpected output occurred']);
} else {
    echo $output;
}