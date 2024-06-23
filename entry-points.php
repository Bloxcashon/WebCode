<?php
require_once 'config.php';
require_once 'upgiveaway.php';
$conn = db_connect();

$_SESSION['giveawayId'] = getGiveawayId($conn);

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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BLOXCASHON</title>
    <link rel="stylesheet" href="entry-points.css">
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
    <div class="giveaway-container">
    <div class="giveaway-header">
        <div class="giveaway-col">
            <h2>CLICKS</h2>
            <p>Earn entry points just for clicking! It's so easy🤗</p>
            <hr>
            <h1>Get 1 point for clicking on the below three links</h1>
            <a href="https://shasogna.com/4/7201947" target="_blank">
            <button class="enter-btn">
            <i class="ri-links-fill &#xF20A;"></i> LINK 1
        </button>
            </a>
        <button class="enter-btn">
        <i class="ri-links-fill &#xF20A;"></i> LINK 2
        </button>
        <button class="enter-btn">
            <i class="ri-links-fill &#xF20A;"></i> LINK 3
        </button>
        <h1> Click on a link and leave the new tab open for 5minutes. Wondering how this works? The link is completely safe and opens up a tab where our partners display their ads, we get paid and we pay you. You can earn a maximum of 5points via link clicks per giveaway round!</h1>

        </div>
        <div class="separator"></div>
        <div class="giveaway-col">
            <h2>SUPPORT US</h2>
            <p>Buy an amazing item from our partners for cheap to show some love!🤍</p>
            <hr>
            <h1> Join the group and earn 1 point then purchase an item and earn 5 points. Points will be automatically updated when your purchase is confirmed. which is immediately!</h1>
            <a href="https://www.roblox.com/groups/7659684/VOID-BLOX" target="_blank">
  <button class="enter-btn">
    <i class="ri-group-2-fill &#xF20A;"></i> JOIN GROUP
  </button>
</a>
        <a href="https://www.roblox.com/groups/7659684/VOID-BLOX#!/store" target="_blank">
        <button class="enter-btn" >
        <i class="ri-shopping-cart-fill &#xF20A;"></i> SHOP ITEMS
        </button>
</a>
        <h1>Thank your for your support. We appreciate it! To show our appreciation, you will always get your 6 entry points automatically for every giveaway round you enter after making a one-time purchase! </h1>
        </div>
        
        
        <div class="separator"></div>
        <div class="giveaway-col">
            <h2>Entries</h2>
            <p id="entries-count"></p>
        </div>
    </div>
    
    <hr>
    <div class="user-entries">
    <?php
        $giveawayId = $_SESSION['giveawayId'];
        $sql = "SELECT points FROM giveaway WHERE unique_id = '$unique_id' AND giveaway_id = '$giveawayId'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if ($row) {
            echo '<p>Your entry points: <span class="entry-points">' . $row['points'] . ' entry point(s)</span></p>';
        } else {
            echo '<p>Your entry points: <span class="entry-points">0 entry points</span></p>';
        }
    ?>
</div>
<div class="giveaway-columns">
    <div class="column">
        <h2>Recent Entries</h2>
        <ul id="recent-entries-list"></ul>
    </div>
    <div class="column">
        <h2>Highest Points</h2>
        <ul id="highest-points-list"></ul>
    </div>
    <div class="column">
        <!-- Add your third column here later -->
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script>
    let giveawayId = '<?php echo $_SESSION['giveawayId']; ?>';
        let entriesCount = 0;

        function updateEntriesCount() {
            $.ajax({
                type: "POST",
                url: "giveaway_entries.php",
                data: { giveawayId: giveawayId },
                success: function(data) {
                    $("#entries-count").text(data);
                    entriesCount = data;
                }
            });
        }

        updateEntriesCount();
        setInterval(updateEntriesCount, 10000);

  

function fetchRecentEntries() {
    $.ajax({
        type: "POST",
        url: "get_recent_entries.php",
        data: { giveawayId: giveawayId },
        success: function(data) {
            $("#recent-entries-list").html(data);
        }
    });
}

fetchRecentEntries();
setInterval(fetchRecentEntries, 10000); 

function fetchHighestPoints() {
    $.ajax({
        type: "POST",
        url: "get_highest_points.php",
        data: { giveawayId: giveawayId },
        success: function(data) {
            $("#highest-points-list").html(data);
        }
    });
}

fetchHighestPoints();
setInterval(fetchHighestPoints, 10000); 

</script>
</body>
</html>