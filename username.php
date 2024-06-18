<?php
require_once 'config.php';
$conn = db_connect();

function generateUniqueId($length = 3, $maxLength = 17) {
    $idLength = rand($length, $maxLength);
    $possibleChars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $uniqueId = '';

    for ($i = 0; $i < $idLength; $i++) {
        $uniqueId .= $possibleChars[mt_rand(0, strlen($possibleChars) - 1)];
    }

    return $uniqueId;
}

if (isset($_POST['form__input'])) {
    $roblox_username = mysqli_real_escape_string($conn, $_POST['form__input']);
    $token = $_SESSION['user_token'];

    // Fetch Roblox user ID from the username
    $roblox_id = fetchRobloxIdFromUsername($roblox_username);

    if ($roblox_id) {
        // Check if the Roblox username already exists in the users table
        if (checkRobloxUsernameExists($roblox_username, $token)) {
            // If the username exists under the same email used for Google Auth, allow the user to proceed
            $sql = "UPDATE users SET roblox_username = '$roblox_username', user_id = '$roblox_id' WHERE token = '$token'";
            $result = mysqli_query($conn, $sql);

            if ($result) {
                // Fetch Roblox avatar image URL
                $roblox_avatar_url = fetchRobloxAvatarUrl($roblox_id);

                if ($roblox_avatar_url) {
                    $sql = "UPDATE users SET roblox_avatar_url = '$roblox_avatar_url' WHERE token = '$token'";
                    $result = mysqli_query($conn, $sql);
                }

                $sql = "SELECT * FROM users WHERE token = '$token'";
                $result = mysqli_query($conn, $sql);
                $user = mysqli_fetch_assoc($result);

                if (!$user['unique_id']) {
                    $unique_id = generateUniqueId(3, 17);
                    $sql = "UPDATE users SET unique_id = '$unique_id' WHERE token = '$token'";
                    $result = mysqli_query($conn, $sql);
                }
            
                $sql = "SELECT user_id, unique_id FROM users WHERE token = '$token'";
                $result = mysqli_query($conn, $sql);
                $user = mysqli_fetch_assoc($result);
            
                $sql = "SELECT unique_id FROM userpoints WHERE unique_id = '$user[unique_id]'";
                $result = mysqli_query($conn, $sql);
            
                if (mysqli_num_rows($result) == 0) {
                    $sql = "INSERT INTO userpoints (user_id, unique_id) VALUES ('$user[user_id]', '$user[unique_id]')";
                    $result = mysqli_query($conn, $sql);
                }
            
                header("Location: next.php");
                die();
                
            } else {
                echo "Error updating Roblox username: " . mysqli_error($conn);
                die();
            }
        } else {
            $sql = "SELECT email FROM users WHERE roblox_username = '$roblox_username' AND token != '$token'";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
                $existing_email_row = mysqli_fetch_assoc($result);
                $existing_email = $existing_email_row['email'];
                $local_part = explode('@', $existing_email)[0];
                $masked_email = substr($local_part, 0, 2) . str_repeat('*', strlen($local_part) - 4) . substr($local_part, -2) . '@gmail.com';
            
                $userinfo = [
                    'email' => $existing_email,
                    'token' => $token
                ];
            
                $sql = "SELECT roblox_username FROM users WHERE email = '{$userinfo['email']}' AND token != '$token'";
                $result = mysqli_query($conn, $sql);
            
                if (mysqli_num_rows($result) > 0) {
                    $existing_roblox_username_row = mysqli_fetch_assoc($result);
                    $existing_roblox_username = $existing_roblox_username_row['roblox_username'];
                    $masked_roblox_username = substr($existing_roblox_username, 0, 2) . str_repeat('*', strlen($existing_roblox_username) - 4) . substr($existing_roblox_username, -2);
                }
            }?>


<style>
    .error-container {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        z-index: 1000;
    }

    .error-container i {
        margin-right: 10px;
    }

    .back-button {
        align-items: center;
        appearance: none;
        background-image: radial-gradient(100% 100% at 100% 0, #5adaff 0, #5468ff 100%);
        border: 0;
        border-radius: 6px;
        box-shadow: rgba(45, 35, 66, 0.4) 0 2px 4px, rgba(45, 35, 66, 0.3) 0 7px 13px -3px, rgba(58, 65, 111, 0.5) 0 -3px 0 inset;
        box-sizing: border-box;
        color: #fff;
        cursor: pointer;
        display: inline-flex;
        font-family: "JetBrains Mono", monospace;
        height: 48px;
        justify-content: center;
        line-height: 1;
        list-style: none;
        overflow: hidden;
        padding-left: 16px;
        padding-right: 16px;
        position: relative;
        text-align: left;
        text-decoration: none;
        transition: box-shadow 0.15s, transform 0.15s;
        user-select: none;
        -webkit-user-select: none;
        touch-action: manipulation;
        white-space: nowrap;
        will-change: box-shadow, transform;
        font-size: 18px;
    }

    .error-container:nth-of-type(1) {
        top: 40%;
    }

    .error-container:nth-of-type(2) {
        top: 60%;
    }

    .back-button:focus {
        box-shadow: #3c4fe0 0 0 0 1.5px inset, rgba(45, 35, 66, 0.4) 0 2px 4px, rgba(45, 35, 66, 0.3) 0 7px 13px -3px, #3c4fe0 0 -3px 0 inset;
    }

    .back-button:hover {
        box-shadow: rgba(45, 35, 66, 0.4) 0 4px 8px, rgba(45, 35, 66, 0.3) 0 7px 13px -3px, #3c4fe0 0 -3px 0 inset;
        transform: translateY(-2px);
    }

    .back-button:active {
        box-shadow: #3c4fe0 0 3px 7px inset;
        transform: translateY(2px);
    }
</style>

<div class="error-container">
    <i class="fa-solid fa-face-dotted"></i>
    Error: The Roblox user already exists in the system under a different email. Kindly go back and login using the right email, <?=$masked_email;?>.
    <button class="back-button" onclick="location.href='index.php';">Back</button>
</div>

        <?php

            // Delete the new email from the database
            $sql = "DELETE FROM users WHERE token = '$token'";
            $result = mysqli_query($conn, $sql);
            unset($_SESSION['user_token']);
            die();
        }
    }
}

// Function to check if the Roblox username already exists in the users table
function checkRobloxUsernameExists($roblox_username, $token) {
    $sql = "SELECT * FROM users WHERE roblox_username = '$roblox_username' AND token != '$token'";
    $result = mysqli_query(db_connect(), $sql);
    return mysqli_num_rows($result) == 0;
}

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token['access_token']);
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    $userinfo = [
        'email' => $google_account_info['email'],
        'picture' => $google_account_info['picture'],
        'token' => $google_account_info['id'],
    ];

    $sql = "SELECT * FROM users WHERE email ='{$userinfo['email']}'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $userinfo = mysqli_fetch_assoc($result);
        $token = $userinfo['token'];
    } else {
        $sql = "INSERT INTO users (email, picture, token) VALUES ('{$userinfo['email']}','{$userinfo['picture']}','{$userinfo['token']}')";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $token = $userinfo['token'];
        } else {
            echo "User is not yet created";
            die();
        }
    }

    $_SESSION['user_token'] = $token;
    header("Location: username.php");
    die();
} else {
    if (!isset($_SESSION['user_token'])) {
        header("Location: index.php");
        die();
    }
    
    $sql = "SELECT * FROM users WHERE token ='{$_SESSION['user_token']}'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $userinfo = mysqli_fetch_assoc($result);
    } else {
        header("Location: index.php");
        die();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RBX Username</title>
    <link rel="stylesheet" href="username.css">
    <link rel="stylesheet" href="animated-input-field.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@200..700&family=Poetsen+One&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</head>
<body>
    <header class="topbar">
        <div class="logo-container">
            <a href="#" class="logo">BLOXCASHON</a>
        </div>
    </header>
    <div class="container">
        <img src="<?= $userinfo['picture'] ?>" alt="" class="profile-picture" width="500" height="500">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <div class="form">
                <div class="input-group">
                    <input type="text" id="form__input" name="form__input" class="form__input" required>
                    <label for="form__input" class="form__label">Roblox Username</label>
                </div>
                <button class="button-29" role="button">Next</button>
            </div>
        </form>
        <a href="logout.php">Logout</a>
        <button class="back-button" onclick="location.href='index.php';">
            <i class="fa-solid fa-arrow-left"></i> Back
        </button>
    </div>
</body>
</html>

<?php

function fetchRobloxIdFromUsername($username) {
    $api_url = "https://users.roblox.com/v1/usernames/users";
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['usernames' => [$username]]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    ]);
    $response = curl_exec($curl);
    if ($response === false) {
        echo "Error fetching Roblox user ID: " . curl_error($curl);
        die();
    }
    $response = json_decode($response, true);
    if (isset($response['data'][0]['id'])) {
        return $response['data'][0]['id'];
    } else {
        echo "Error fetching Roblox user ID.";
        die();
    }
}

function fetchRobloxAvatarUrl($userId) {
    $avatar_url = "https://rbx.how/user/{$userId}";
    return $avatar_url;
}