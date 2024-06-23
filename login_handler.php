<?php
header('Content-Type: application/json');
require_once 'config.php';

function verify_hcaptcha($response) {
    $secret = 'ES_f4758490d39d4ebd9573fbffd7f5d269';
    $verify = file_get_contents("https://hcaptcha.com/siteverify?secret={$secret}&response={$response}");
    $captcha_success = json_decode($verify);
    return $captcha_success->success;
}

try {
    $conn = db_connect();

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
        // Verify hCaptcha
        if (!isset($_POST['h-captcha-response']) || !verify_hcaptcha($_POST['h-captcha-response'])) {
            echo json_encode(['success' => false, 'error' => "Invalid captcha. Please try again."]);
            exit;
        }

        $username = $_POST['username'];
        $sql = "SELECT * FROM users WHERE roblox_username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_token'] = $user['token'];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => "User account does not exist. Kindly create one by signing up with Google first."]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => "Invalid request"]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => "An error occurred: " . $e->getMessage()]);
}