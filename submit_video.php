<?php
require_once 'config.php';
$conn = db_connect();

if (!isset($_SESSION['user_token'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

function isValidYoutubeUrl($url) {
    $regex = '/^(?:https?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=)?(?:embed\/)?(?:v\/)?(?:shorts\/)?(?:\S+)$/';
    return preg_match($regex, $url);
}

function isValidTikTokUrl($url) {
    $regex = '/^(?:https?:\/\/)?(?:www\.)?(?:tiktok\.com)\/(@[\w.-]+)\/video\/(\d+)/';
    return preg_match($regex, $url);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $video_url = mysqli_real_escape_string($conn, $_POST['video_url']);
    $platform = mysqli_real_escape_string($conn, $_POST['platform']);
    $compid = mysqli_real_escape_string($conn, $_POST['compid']);
    $hcaptcha_response = $_POST['h-captcha-response'];

    // Get the unique_id from the users table
    $user_token = $_SESSION['user_token'];
    $user_query = "SELECT unique_id FROM users WHERE token = '$user_token'";
    $user_result = mysqli_query($conn, $user_query);
    
    if (!$user_result || mysqli_num_rows($user_result) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        exit;
    }
    
    $user_data = mysqli_fetch_assoc($user_result);
    $unique_id = $user_data['unique_id'];

    // Verify URL
    if ($platform === 'youtube' && !isValidYoutubeUrl($video_url)) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a valid YouTube URL']);
        exit;
    } elseif ($platform === 'tiktok' && !isValidTikTokUrl($video_url)) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a valid TikTok URL']);
        exit;
    }

    // Verify hCaptcha
    $secret = 'ES_f4758490d39d4ebd9573fbffd7f5d269';
    $verify_url = 'https://hcaptcha.com/siteverify';
    $data = array(
        'secret' => $secret,
        'response' => $hcaptcha_response
    );

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );

    $context  = stream_context_create($options);
    $result = file_get_contents($verify_url, false, $context);
    $response_data = json_decode($result);

    if (!$response_data->success) {
        echo json_encode(['status' => 'error', 'message' => 'Captcha verification failed']);
        exit;
    }

    // Check for existing submissions
    $table_name = $platform . '_submissions';
    $comp_id_field = $platform === 'youtube' ? 'ytcompid' : 'tkcompid';
    $check_sql = "SELECT COUNT(*) as count FROM $table_name WHERE unique_id = '$unique_id' AND $comp_id_field = '$compid'";
    $check_result = mysqli_query($conn, $check_sql);
    $check_data = mysqli_fetch_assoc($check_result);

    if ($check_data['count'] >= 3) {
        echo json_encode(['status' => 'error', 'message' => 'You have already submitted the maximum of 3 videos for this competition.']);
        exit;
    }

    $sql = "INSERT INTO $table_name (unique_id, video_url, $comp_id_field) VALUES ('$unique_id', '$video_url', '$compid')";
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'success', 'message' => 'Video submitted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error submitting video']);
    }
}
exit();
?>