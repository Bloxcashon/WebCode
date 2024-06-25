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

// Default to YouTube competition
$platform = isset($_GET['platform']) ? $_GET['platform'] : 'youtube';

?>
<!DOCTYPE html>
<html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BLOXCASHON - Video Competitions</title>
    <link rel="stylesheet" href="yt.css">
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@200..700&family=Poetsen+One&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <style>
        .platform-selector {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .platform-button {
            padding: 10px 20px;
            margin: 0 10px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .platform-button.active {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
<header class="nav">
    <a href="#" class="logo">BLOXCASHON</a>
    <ul class="navlist">
        <li><a href="#">Earn</a></li>
        <li><a href="#">Promocodes</a></li>
        <li><a href="#">Withdraw</a></li>
        <li><a href="#">Giveways</a></li>
        <li><a href="#">Rewards</a></li>
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
    <div class="platform-selector">
        <button class="platform-button <?php echo $platform === 'youtube' ? 'active' : ''; ?>" onclick="changePlatform('youtube')">YouTube</button>
        <button class="platform-button <?php echo $platform === 'tiktok' ? 'active' : ''; ?>" onclick="changePlatform('tiktok')">TikTok</button>
    </div>
    <div class="video-competition">
    <h2><?php echo ucfirst($platform); ?> Competition</h2>
    <?php
    // Fetch current competition details
    $table_prefix = $platform === 'youtube' ? 'yt' : 'tk';
    $comp_query = "SELECT * FROM {$platform}_competitions WHERE end_date > NOW() ORDER BY start_date DESC LIMIT 1";
    $comp_result = mysqli_query($conn, $comp_query);
    $comp_data = mysqli_fetch_assoc($comp_result);

    if ($comp_data) {
        $compid = $comp_data["{$table_prefix}compid"];
        $end_date = strtotime($comp_data['end_date']);
        $current_time = time();
        $time_remaining = $end_date - $current_time;

        // Count entries
        $entries_query = "SELECT COUNT(*) as total_entries FROM {$platform}_submissions WHERE {$table_prefix}compid = $compid";
        $entries_result = mysqli_query($conn, $entries_query);
        $entries_data = mysqli_fetch_assoc($entries_result);
        $total_entries = $entries_data['total_entries'];

        // Display competition info
        ?>
        <div class="competition-form">
            <input type="text" id="video_url" placeholder="Enter your <?php echo ucfirst($platform); ?> URL here...">
            <p><br>Make a <?php echo ucfirst($platform); ?> Video about our site and have a chance to win <?php echo $comp_data['amount']; ?> R$.</br><br>*General Video Guidelines:</br><br>•Include your Roblox Username in the video description for validation</br><br>•If you win, leave the video as public for a minimum of 2 months after payment. Failure to which, the amount paid will be withdrawn from you</br><br>•Good Luck to All Participants!</br></p>
            <div class="captcha">
                <div class="h-captcha" data-sitekey="1de85670-0cdb-46e7-aa7d-659d4d9c4e06"></div>
            </div>
            <button onclick="submitVideo()">Submit</button>
        </div>
        <div class="competition-info">
            <div class="info-box">
                <h3>Prize</h3>
                <img src="images/rbx.png" alt="Prize">
                <p class="amount"><?php echo $comp_data['amount']; ?> R$</p>
                <p>To be Won</p>
            </div>
            <div class="info-box">
                <h3>Entries</h3>
                <img src="images/raffles.png" alt="Entries">
                <p class="amount"><?php echo $total_entries; ?></p>
                <p>Total Entries</p>
            </div>
            <div class="info-box">
                <h3>End Time</h3>
                <img src="images/timer.png" alt="Clock">
                <p class="time" id="countdown"></p>
                <p>Remaining</p>
            </div>
        </div>
        <script>
        var countDownDate = <?php echo $end_date * 1000; ?>;
        var x = setInterval(function() {
            var now = new Date().getTime();
            var distance = countDownDate - now;
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
            document.getElementById('countdown').innerHTML = days + "D " + hours + "H " + minutes + "M " + seconds + "S ";
            if (distance < 0) {
                clearInterval(x);
                document.getElementById('countdown').innerHTML = "EXPIRED";
            }
        }, 1000);

        function isValidYoutubeUrl(url) {
            var regExp = /^(?:https?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=)?(?:embed\/)?(?:v\/)?(?:shorts\/)?(?:\S+)$/;
            return regExp.test(url);
        }

        function isValidTikTokUrl(url) {
            var regExp = /^(?:https?:\/\/)?(?:www\.)?(?:tiktok\.com)\/(@[\w.-]+)\/video\/(\d+)/;
            return regExp.test(url);
        }

        function submitVideo() {
            var video_url = document.getElementById('video_url').value;
            var hcaptchaResponse = hcaptcha.getResponse();
            var platform = '<?php echo $platform; ?>';

            if (platform === 'youtube' && !isValidYoutubeUrl(video_url)) {
                Swal.fire('Error', 'Please enter a valid YouTube URL', 'error');
                return;
            } else if (platform === 'tiktok' && !isValidTikTokUrl(video_url)) {
                Swal.fire('Error', 'Please enter a valid TikTok URL', 'error');
                return;
            }

            if (!hcaptchaResponse) {
                Swal.fire('Error', 'Please complete the captcha', 'error');
                return;
            }

            fetch('submit_video.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'video_url=' + encodeURIComponent(video_url) + 
                      '&compid=<?php echo $compid; ?>' + 
                      '&h-captcha-response=' + encodeURIComponent(hcaptchaResponse) +
                      '&platform=' + encodeURIComponent(platform)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('Success', data.message, 'success');
                    hcaptcha.reset();
                } else {
                    Swal.fire('Error', data.message, 'error');
                    hcaptcha.reset();
                }
            });
        }

        </script>
    <?php
    } else {
        echo "<p>No active " . ucfirst($platform) . " competition at the moment.</p>";
    }
    ?>
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
    function changePlatform(newPlatform) {
        window.location.href = '?platform=' + newPlatform;
    }
</script>

</body>
</html>

