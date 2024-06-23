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
    $referrer_id = getUserReferrer($unique_id, $conn);
} else {
    header("Location: index.php");
    die();
}

$roblox_avatar_url = $userinfo['roblox_avatar_url'];

function validatePromoCode($code, $uid) {
    global $conn;
    $sql = "SELECT * FROM promocodes WHERE code = '$code'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $promoCode = mysqli_fetch_assoc($result);
        if ($promoCode['expiry_date'] >= date('Y-m-d')) {
            // Check if the user has already redeemed the code
            $sql = "SELECT * FROM earned WHERE unique_id = '$uid' AND url = '$code'";
            $result = mysqli_query($conn, $sql);
            if (mysqli_num_rows($result) > 0) {
                return false; // User has already redeemed the code
            } else {
                return $promoCode['points'];
            }
        } else {
            return false; // Promo code has expired
        }
    } else {
        return false; // Promo code not found
    }
}

function getUserReferrer($unique_id, $conn) {
    $sql = "SELECT referrer_id FROM referrals WHERE referred_id = '$unique_id'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $referrer_info = mysqli_fetch_assoc($result);
        return $referrer_info['referrer_id'];
    } else {
        return null; // User does not have a referrer
    }
}

// Handle promo code submission
if (isset($_POST['promocode'])) {
    $code = $_POST['promocode'];
    $uid = $userinfo['unique_id'];
    $points = validatePromoCode($code, $uid);
    if ($points !== false) {

        $sql = "INSERT INTO earned (unique_id, earnings, type, url, time, date) VALUES (?,?,?,?, NOW(), NOW())";
$stmt = mysqli_prepare($conn, $sql);
$uid= &$uid;
$points= &$points;
$type= 'promocode';
mysqli_stmt_bind_param($stmt, "sdss", $uid, $points, $type, $code);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);


        // Update userpoints table
        $sql = "UPDATE userpoints SET codes = codes +?, total = total +?, balance = total - withdrawn WHERE unique_id = '$uid'";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "dd", $points, $points);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Check if the user has a referrer
        if ($referrer_id !== null) {
            // Calculate referral earnings (10% of user's earnings)
            $referral_earnings = $points * 0.1;

            // Insert new record into the refearn table
            $sql = "INSERT INTO refearn (referrer_id, referred_id, earnings, created_at) VALUES (?,?,?, NOW())";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssd", $referrer_id, $uid, $referral_earnings);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // Insert new record into the earned table for the referrer
            $uid_ref = $referrer_id;
            $points_ref = $referral_earnings;
            $type_ref = 'referral';
            $code_ref = $code;
            
            $sql = "INSERT INTO earned (unique_id, earnings, type, url, time, date) VALUES (?,?,?,?, NOW(), NOW())";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sdss", $uid_ref, $points_ref, $type_ref, $code_ref);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // Update the referrer's balance in userpoints table
            $sql = "UPDATE userpoints SET refbonus = refbonus +?, total = total +?, balance = total - withdrawn WHERE unique_id = '$referrer_id'";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "dd", $referral_earnings, $referral_earnings);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        $success_message = "Promo code redeemed successfully! You earned $points points.";
        echo $success_message;
    } else {
        if (mysqli_num_rows(mysqli_query($conn, "SELECT * FROM earned WHERE unique_id = '$uid' AND url = '$code'")) > 0) {
            $error_message = 'You already redeemed this code.';
        } elseif (mysqli_num_rows(mysqli_query($conn, "SELECT * FROM promocodes WHERE code = '$code' AND expiry_date < NOW()")) > 0) {
            $error_message = 'This promocode is expired.';
        } else {
            $error_message = 'Invalid promo code.';
        }
        echo $error_message;
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BLOXCASHON - Promocodes</title>
    <link rel="stylesheet" href="promocodes.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@200..700&family=Poetsen+One&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</head>
<body>
<header class="nav">
    <a href="#" class="logo">BLOXCASHON</a>
    <ul class="navlist">
        <li><a href="main.php">Earn</a></li>
        <li><a href="promocodes.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'promocodes.php')? 'active' : '';?>">Promocodes</a></li>
        <li><a href="#">Withdraw</a></li>
        <li><a href="#">Giveways</a></li>
        <li><a href="#">Rewards</a></li>
        <li><a href="#">Referrals</a></li>
        <li><a href="#">Tasks</a></li>
    </ul>

    <div class="bx bx-menu" id="menu-icon"></div>
</header>
<section class="promocodes" id="promocodes">
    <div class="main-container">
        <div class="container">
            <img src="<?php echo $roblox_avatar_url;?>" alt="Roblox Avatar" class="profile-picture">
            <h1>Welcome, <?php echo $userinfo['roblox_username'];?>!</h1>
            <p>Your Roblox ID is: <?php echo $userinfo['user_id'];?></p>
        </div>

        <div class="promocode-container" id="promocode-container">
            <h2>Redeem your Promocode here</h2>
            <input type="text" placeholder="Enter your promocode" id="promocode-input">
            <button class="redeem-button" id="redeem-button">Redeem</button>
            <p>Follow our socials to get the latest promocodes and news:</p>
            <div class="social-links">
                <a href="https://discord.gg/2r8adaxteD" target="_blank" class="social-btn social-btn--discord">
                    <i class="ri-discord-fill"> Join Server</i>
                </a>
                <a href="https://www.youtube.com/channel/UC_tqQ8bcQ-PSdV9U0ZJPA1g?sub_confirmation=1" target="_blank" class="social-btn social-btn--youtube">
                    <i class="ri-youtube-fill"> Subscribe</i>
                </a>
                <a href="https://x.com/bloxcashon" target="_blank" class="social-btn social-btn--x">
                    <i class="ri-twitter-x-line"> Follow</i>
                </a>
                <a href="https://www.instagram.com/bloxcashon/" target="_blank" class="social-btn social-btn--instagram">
                    <i class="ri-instagram-fill"> Follow</i>
                </a>
                <a href="https://www.facebook.com/bloxcashon" target="_blank" class="social-btn social-btn--facebook">
                    <i class="ri-facebook-fill"> Follow</i>
                </a>
                <a href="https://www.tiktok.com/@bloxcashon" target="_blank" class="social-btn social-btn--tiktok">
                    <i class="ri-tiktok-fill"> Follow</i>
                </a>
                </div>
       </div>
   </div>
</section>

<footer>
  <div class="footer-container">
    <div class="footer-left-wrapper">
    <div class="footer-left">
      <img src="images/logo.png" alt="Logo" class="logo">
      <p style="text-align: center;">&copy; 2024 Bloxcashon</p>
      <p style="text-align: center;">All rights reserved</p>
    </div>
</div>
    <div class="footer-right">
      <div class="footer-links">
        <a href="tos.html">Terms & Conditions</a>
        <i class="ri-git-commit-fill"></i>
        <a href="privacy.html">Privacy Policy</a>
        <i class="ri-git-commit-fill"></i>
        <a href="return.html">Return Policy</a>
        <i class="ri-git-commit-fill"></i>
        <a href="eula">EULA</a>
      </div>
      <p>Made with <i class="bx bx-heart" style="color: #fff;"></i> by Bloxcashon</p>
      <p>We are not affiliated with Roblox or any of their trademarks</p>
    </div>
  </div>
</footer>

<script>
   document.getElementById('redeem-button').addEventListener('click', function() {
       var promocode = document.getElementById('promocode-input').value;
       var xhr = new XMLHttpRequest();
       xhr.open('POST', 'promocodes.php', true);
       xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
       xhr.send('promocode=' + encodeURIComponent(promocode));
       xhr.onload = function() {
           if (xhr.status === 200) {
               var response = xhr.responseText;
               if (response.includes('Promo code redeemed successfully')) {
                   displaySuccessAlert(response);
               } else {
                   displayErrorAlert(response);
               }
           }
       };
   });

   function displaySuccessAlert(message) {
       Swal.fire({
           icon: 'success',
           title: 'Success!',
           text: message,
           confirmButtonText: 'Close',
           confirmButtonColor: '#3085d6'
       });
   }

   function displayErrorAlert(message) {
       Swal.fire({
           icon: 'error',
           title: 'Oops...',
           text: message,
           confirmButtonText: 'Close',
           confirmButtonColor: '#d33'
       });
   }
</script>
</body>
</html>