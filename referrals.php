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
    $unique_id = $userinfo['unique_id'];
} else {
    header("Location: index.php");
    die();
}

$roblox_avatar_url = $userinfo['roblox_avatar_url'];

// Fetch referral statistics
$num_users_referred = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM referrals WHERE referrer_id = '$unique_id'"));
$total_referral_earnings = mysqli_fetch_row(mysqli_query($conn, "SELECT SUM(refbonus) FROM userpoints WHERE unique_id = '$unique_id'"))[0];

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BLOXCASHON</title>
    <link rel="stylesheet" href="referrals.css">
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
        <li><a href="#">Earn</a></li>
        <li><a href="#">Promocodes</a></li>
        <li><a href="#">Withdraw</a></li>
        <li><a href="#">Giveways</a></li>
        <li><a href="#">Airdrop</a></li>
        <li><a href="#">Referrals</a></li>
        <li><a href="#">Tasks</a></li>
    </ul>

    <div class="bx bx-menu" id="menu-icon"></div>
</header>
<section class="emethods" id="emethods">
<div class="main-container">
<div class="container">
        <img src="<?php echo $roblox_avatar_url;?>" alt="Roblox Avatar" class="profile-picture">
        <h1>Welcome, <?php echo $userinfo['roblox_username'];?>!</h1>
        <p>Your Roblox ID is: <?php echo $userinfo['user_id'];?>
      </p>

      <a class="button-29" id="balance-button">
    <?php
        $balance_sql = "SELECT balance FROM userpoints WHERE unique_id = '$unique_id'";
        $balance_result = mysqli_query($conn, $balance_sql);
        $balance_row = mysqli_fetch_assoc($balance_result);
        echo $balance_row['balance']. ' R$';
    ?>
</a>
</p>
<a href="logout.php" class="button-28">Log Out</a>
    </div>

        
    <div class="emethods-box">
            <div class="referral-container">
            <h2>Referrals</h2>
  <p>Invite new users to our website with your referral link and receive 10% of all their earnings!</p>
  <ul>
    <li><i class='bx bx-check'></i> Earn 10% of referral earnings for life!</li>
    <li><i class='bx bx-check'></i> Referral earnings will be instantly credited.</li>
    <li><i class='bx bx-check'></i> Gaming Youtube channels are welcome.</li>
    <li><i class='bx bx-check'></i> The user must be new to our website.</li>
  </ul>
  <p>Your Invitation Link: (click link to copy)</p>
  <div class="referral-link" id="referral-link-text" onclick="copyToClipboard('referral-link-text')">http://localhost/WebCode/index.php?ref=<?php echo $unique_id;?></div>
  <div class="referral-stats">
    <div class="users-referred">
      <span>Total Users Referred</span><br>
      <span><?php echo $num_users_referred;?></span></br>
    </div>
    <div class="earnings">
      <span>Total Earned From Referrals</span><br>
      <span>R$ <?php echo number_format($total_referral_earnings, 2);?></span></br>
    </div>
  </div>
</div>
<div id="alert-box" class="alert-box">
        <span class="alert-message">Referral link copied to clipboard!</span>
        <span class="close-btn">&times;</span>
    </div>
</section>
<script>
        function copyToClipboard(elementId) {
            var element = document.getElementById(elementId);
            var range = document.createRange();
            range.selectNode(element);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
            document.execCommand("copy");
            showAlert();
        }

        function showAlert() {
            var alertBox = document.getElementById("alert-box");
            alertBox.style.display = "block";

            var closeBtn = document.querySelector(".close-btn");
            closeBtn.addEventListener("click", hideAlert);

            setTimeout(hideAlert, 10000);
        }

        function hideAlert() {
            var alertBox = document.getElementById("alert-box");
            alertBox.style.display = "none";
        }
    </script>

</body>
</html>