<?php
require_once 'config.php';
$conn = db_connect();

if (!isset($_SESSION['user_token'])) {
    header("Location: index.php");
    die();
}

$sql = "SELECT * FROM users WHERE token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['user_token']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $userinfo = $result->fetch_assoc();
    $unique_id = $userinfo['unique_id'];
    $_SESSION['balance'] = $userinfo['balance'];
} else {
    header("Location: index.php");
    die();
}

$roblox_avatar_url = $userinfo['roblox_avatar_url'];

// Console log the $userinfo array
echo "<script>console.log('User Information:', " . json_encode($userinfo) . ");</script>";

// Fetch referrer details and store in session
$sql = "SELECT referrer_id FROM referrals WHERE referred_id = '$unique_id'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    $referrer_info = mysqli_fetch_assoc($result);
    $_SESSION['referrer_id'] = $referrer_info['referrer_id'];
    // Console log the $referrer_info array
    echo "<script>console.log('Referrer Information:', " . json_encode($referrer_info) . ");</script>";
} else {
    $_SESSION['referrer_id'] = null; // User does not have a referrer
    echo "<script>console.log('User does not have a referrer.');</script>";
}

// Update earnings when user clicks on bonus links
// Update earnings when user clicks on bonus links
if (isset($_GET['bonus_link'])) {
    $earnings = 0.5;
    $type = 'bonus_link';
    $url = $_GET['bonus_link'];
    $time = date('Y-m-d H:i:s');
    $date = date('Y-m-d');

    // Check if the user has already clicked on the same bonus link
    $sql = "SELECT * FROM earned WHERE unique_id = '$unique_id' AND type = '$type' AND url = '$url'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        // User has already clicked on the same bonus link, do nothing
    } else {
        // Insert new record into the earned table
        $sql = "INSERT INTO earned (unique_id, earnings, type, url, time, date) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $unique_id, $earnings, $type, $url, $time, $date);
        $stmt->execute();

        // Check if the user has a referrer
        if ($_SESSION['referrer_id'] !== null) {
            $referrer_id = $_SESSION['referrer_id'];

            // Calculate referral earnings (10% of user's earnings)
            $referral_earnings = $earnings * 0.1;

            // Update the referrer's points in the userpoints table
            $sql = "UPDATE userpoints SET refbonus = refbonus + ?, total = emethod + sclink + refbonus + codes, balance = total - withdrawn WHERE unique_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ds", $referral_earnings, $referrer_id);
            $stmt->execute();

            // Insert new record into the refearn table
            $sql = "INSERT INTO refearn (referrer_id, referred_id, earnings, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssd", $referrer_id, $unique_id, $referral_earnings);
            $stmt->execute();

            // Insert new record into the earned table for the referrer
            $referrer_type = 'referral';
            $referrer_url = $unique_id;
            $sql = "INSERT INTO earned (unique_id, earnings, type, url, time, date) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $referrer_id, $referral_earnings, $referrer_type, $referrer_url, $time, $date);
            $stmt->execute();
        }

        // Update the user's points in the userpoints table
        if ($type == 'bonus_link') {
            $sql = "UPDATE userpoints SET sclink = sclink + ?, total = emethod + sclink + refbonus + codes, balance = total - withdrawn WHERE unique_id = ?";
        } elseif ($type == 'referral') {
            $sql = "UPDATE userpoints SET refbonus = refbonus + ?, total = emethod + sclink + refbonus + codes, balance = total - withdrawn WHERE unique_id = ?";
        } elseif ($type == 'promocode') {
            $sql = "UPDATE userpoints SET codes = codes + ?, total = emethod + sclink + refbonus + codes, balance = total - withdrawn WHERE unique_id = ?";
        }
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ds", $earnings, $unique_id);
        $stmt->execute();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BLOXCASHON</title>
    <link rel="stylesheet" href="stylemain.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@200..700&family=Poetsen+One&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</head>
<body>
<header class="nav">
    <a href="#" class="logo">BLOXCASHON</a>
    <ul class="navlist">
        <li><a href="main.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'main.php')? 'active' : '';?>">Earn</a></li>
        <li><a href="promocodes.php">Promocodes</a></li>
        <li><a href="withdraw.php">Withdraw</a></li>
        <li><a href="#">Giveways</a></li>
        <li><a href="#">Airdrop</a></li>
        <li><a href="referrals.php">Referrals</a></li>
        <li><a href="#">Tasks</a></li>
    </ul>

    <div class="bx bx-menu" id="menu-icon"></div>
</header>
<section class="emethods" id="emethods">
<div class="main-container">
<div class="container">
        <img src="<?php echo $roblox_avatar_url; ?>" alt="Roblox Avatar" class="profile-picture">
        <h1>Welcome, <?php echo $userinfo['roblox_username']; ?>!</h1>
        <p>Your Roblox ID is: <?php echo $userinfo['user_id']; ?>
      </p>
        <a class="button-29"><?php echo $userinfo['balance']; ?></a>
</p>
<a href="logout.php" class="button-28">Log Out</a>
    </div>

        
    <div class="emethods-box">
        <h2 class="heading">Choose Any of The Following Simple Methods and Earn R$ immediately!</h2>
        <div class="wrapper">
            <div class="emethod-item">
                <img src="images/offer-wall.png" alt="" class="profile-picture">
                <h2>Choose any offerwall provider below:</h2>
                <p>Offers are easy guaranteed methods to earn R$ from simple tasks such as playing fun games and downloading light apps.How this Works? You complete an offer. We get paid. We pay you in R$</p>
                <div class="surveyimg">
                <a href="#" class="surveyimgcpx">
                <img src="images/ayestudios.png" alt="" class="surveyimg1">
    </a>
    <a href="#" class="surveyimgcpx">
                <img src="images/adgate.png" alt="" class="surveyimg1">
    </a>
    <a href="#" class="surveyimgcpx">
                <img src="images/mychips.png" alt="" class="surveyimg1">
    </a>
    <a href="#" class="surveyimgcpx">
                <img src="images/admantum.png" alt="" class="surveyimg1">
    </a>
</div>
            </div>
            <div class="emethod-item">
                <img src="images/bonus-icon.png" alt="" class="profile-picture">
                <h2>Get paid R$ just for following our socials:</h2>
                <p>Follow our socials so as not to miss out on exciting news such as promocodes and the best part is, you get paid to do it! What are you waiting for!</p>
                <div class="bonuslinks">
                <?php
    $referrer_id = isset($_SESSION['referrer_id'])? $_SESSION['referrer_id'] : null;
   ?>
                <a href="https://discord.gg/2r8adaxteD" target="_blank" onclick="recordClick('https://discord.gg/2r8adaxteD', '<?php echo $unique_id;?>','<?php echo $_SESSION['referrer_id'];?>')" class="social-btn social-btn--discord">
    <i class="ri-discord-fill"> Join Server +0.5R$</i>
</a>
<a href="https://www.youtube.com/channel/UC_tqQ8bcQ-PSdV9U0ZJPA1g?sub_confirmation=1" target="_blank" onclick="recordClick('https://www.youtube.com/channel/UC_tqQ8bcQ-PSdV9U0ZJPA1g?sub_confirmation=1', '<?php echo $unique_id;?>','<?php echo $_SESSION['referrer_id'];?>')" class="social-btn social-btn--youtube">
    <i class="ri-youtube-fill"> Subscribe +0.5R$</i>
</a>
<a href="https://x.com/bloxcashon" target="_blank" onclick="recordClick('https://x.com/bloxcashon', '<?php echo $unique_id;?>','<?php echo $_SESSION['referrer_id'];?>')" class="social-btn social-btn--x">
    <i class="ri-twitter-x-line"> Follow +0.5R$</i>
</a>
<a href="https://www.instagram.com/bloxcashon/" target="_blank" onclick="recordClick('https://www.instagram.com/bloxcashon/', '<?php echo $unique_id;?>','<?php echo $_SESSION['referrer_id'];?>')" class="social-btn social-btn--instagram">
    <i class="ri-instagram-fill"> Follow +0.5R$</i>
</a>
<a href="https://www.facebook.com/bloxcashon" target="_blank" onclick="recordClick('https://www.facebook.com/bloxcashon', '<?php echo $unique_id;?>','<?php echo $_SESSION['referrer_id'];?>')" class="social-btn social-btn--facebook">
    <i class="ri-facebook-fill"> Follow +0.5R$</i>
</a>
<a href="https://twitter.com/bloxcashon" target="_blank" onclick="recordClick('https://twitter.com/bloxcashon', '<?php echo $unique_id;?>','<?php echo $_SESSION['referrer_id'];?>')" class="social-btn social-btn--twitter">
    <i class="ri-twitter-fill"> Follow +0.5R$</i>
</a>
</div>
            </div>
            <div class="emethod-item">
                <img src="images/survey-icon.png" alt="" class="profile-picture">
                <h2>Choose any survey provider below:</h2>
                <p>Take short and easy surveys. It takes only a couple of minutes, around 5min to complete. You don't have to get the answers right, you get paid for attempting!We are the only R$ earning site that does this!</p>
                <div class="surveyimg">
                <a href="#" class="surveyimgcpx" onclick="submitForm('<?php echo $unique_id;?>', '<?php echo $userinfo['roblox_username'];?>', '<?php echo $userinfo['email'];?>')">
            <img src="images/cpxgreen.png" alt="" class="surveyimg1">
        </a>
                <a href="#" class="surveyimgbl">
                <img src="images/bitlabs.png" alt="" class="surveyimg1">
    </a>
</div>
                </div>
            <div class="emethod-item">
                <img src="images/videos-icon.png" alt="" class="profile-picture">
                <h2>Choose any video provider below:</h2>
                <p>Watch short exciting videos and get paid to do that!</p>
            </div>
        </div>
    </div>
</div>   
</section>
<script>
   function recordClick(url, unique_id, referrer_id) {
       fetch(`record_bonus_link.php?bonus_link=${encodeURIComponent(url)}&unique_id=${encodeURIComponent(unique_id)}&referrer_id=${encodeURIComponent(referrer_id)}`);
   }
</script>
<script>
    function submitForm(unique_id, username, email) {
        var form = document.createElement('form');
        form.method = 'post';
        form.action = 'cpxresearch.php';
        form.innerHTML = `
            <input type="hidden" name="unique_id" value="${unique_id}">
            <input type="hidden" name="username" value="${username}">
            <input type="hidden" name="email" value="${email}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
</script>

</body>
</html>