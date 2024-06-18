<?php
require_once 'config.php';
$conn = db_connect();

if (!isset($_SESSION['user_token'])) {
    header("Location: index.php");
    die();
}

// Get the user info
$sql = "SELECT * FROM users WHERE token ='{$_SESSION['user_token']}'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    $userinfo = mysqli_fetch_assoc($result);
    $roblox_avatar_url = $userinfo['roblox_avatar_url'];
    $unique_id = $userinfo['unique_id'];
} else {
    header("Location: index.php");
    die();
}

// Check if the user was referred
if (isset($_SESSION['referral_id'])) {
    $referral_id = $_SESSION['referral_id'];
    unset($_SESSION['referral_id']); // Remove the referral ID from the session

    // Get the referrer's unique_id
    $referrer_unique_id = get_referrer_unique_id($referral_id, $conn);

    if ($referrer_unique_id !== null) {
        // Update the referrals table
        $sql = "INSERT INTO referrals (referrer_id, referred_id, created_at) VALUES (?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $referrer_unique_id, $unique_id);
        mysqli_stmt_execute($stmt);
    }
}

// Function to get the referrer's unique_id
function get_referrer_unique_id($referral_id, $conn) {
    $sql = "SELECT unique_id FROM users WHERE unique_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $referral_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $referrer_unique_id);
        mysqli_stmt_fetch($stmt);
        return $referrer_unique_id;
    } else {
        return null; // Handle error or return a default value
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Next Page</title>
    <link rel="stylesheet" href="main.css">
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
        <img src="<?php echo $roblox_avatar_url; ?>" alt="Roblox Avatar" class="profile-picture">
        <h1>Welcome, <?php echo $userinfo['roblox_username']; ?>!</h1>
        <p>Your Roblox ID is: <?php echo $userinfo['user_id']; ?></p>
        <a href="main.php" class="button-29">Proceed to Dashboard</a>
        <a href="logout.php" class="button-28">Log Out</a>
    </div>
</body>
</html>