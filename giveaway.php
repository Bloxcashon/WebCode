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
    <link rel="stylesheet" href="giveaway.css">
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
    <div class="giveaway-container">
    <div class="giveaway-header">
        <div class="giveaway-col">
            <h2>Prize</h2>
            <p>1500 R$</p>
        </div>
        <div class="separator"></div>
        <div class="giveaway-col">
            <h2 id="time-heading">Time remaining</h2>
            <p id="time-remaining"></p>
        </div>
        
        <div class="separator"></div>
        <div class="giveaway-col">
            <h2>Entries</h2>
            <p id="entries-count"></p>
        </div>
    </div>
    <hr>
    <div class="giveaway-content">
        <h1>Enter the giveaway to win the prize</h1>
        <p>Entry is free if you have completed at least 2 offers within the past day. Participants with higher entry points stand a higher chance of winning</p>
        <button class="enter-btn" id="enter-button">
        <i class="ri-ticket-2-fill &#xF20A;"></i> ENTER
        </button>
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
    <button class="enter-btn">
        <i class="ri-ticket-2-fill &#xF20A;"></i> GET MORE ENTRY POINTS
        </button>
</div>
</div>
<div class="giveaway-columns">
    <div class="column">
        <h2>Recent Winners</h2>
        <ul id="recent-winners-list"></ul>
    </div>
    <div class="column">
        <h2>Recent Entries</h2>
        <ul id="recent-entries-list"></ul>
    </div>
    <div class="column">
        <h2>Highest Points</h2>
        <ul id="highest-points-list"></ul>
    </div>
</div>
</div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  document.querySelector('.enter-btn').addEventListener('click', function() {
    $.ajax({
        type: 'POST',
        url: 'upgiveaway.php',
        data: { enterGiveaway: true, uniqueId: '<?php echo $unique_id;?>', giveawayId: '<?php echo $_SESSION['giveawayId'];?>' },
        success: function(data) {
            if (data === 'Entry successful!') {
                // Entry was successful, handle accordingly
                console.log(data);
            } else {
                // Entry failed, display an error message or handle accordingly
                console.log(data);
            }
        }
    });
});
</script>

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


    let countdownInterval;
let currentHour = new Date().getUTCHours();
let startTime = new Date().setUTCHours(currentHour, 0, 0, 0); // Set start time to the current hour (e.g., 12:00:00 UTC)
let currentTime = new Date().getTime();
let timeRemaining;
let minutes;
let seconds;

function startCountdown() {
    countdownInterval = setInterval(function() {
        currentTime = new Date().getTime();
        timeRemaining = startTime + 3600000 - currentTime; // Calculate time remaining until the next hour

        minutes = Math.floor((timeRemaining % 3600000) / 60000);
        seconds = Math.floor((timeRemaining % 60000) / 1000);

        if (minutes === 55 && seconds === 0) { // 5 minutes before the next hour
            document.getElementById('time-heading').textContent = "Time to next round";
            document.getElementById('enter-button').style.display = 'none';
            document.getElementById('enter-button').insertAdjacentText('afterend', 'Picking round winner');
        }

        document.querySelector('#time-remaining').innerHTML = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

        if (timeRemaining <= 0) {
            clearInterval(countdownInterval);
            handleWinnerSelection($conn, giveawayId);
            resetGiveaway();
            startTime = new Date().setUTCHours(currentHour + 1, 0, 0, 0); // Set start time for the next hour's giveaway
            currentHour = (currentHour + 1) % 24; // Update currentHour for the next giveaway
            startCountdown();
        }
    }, 1000);
}

function resetGiveaway() {
    // Reset entries count
    $("#entries-count").text('0');

    // Reset giveaway content
    document.getElementById('time-heading').textContent = "Time remaining";
    document.getElementById('enter-button').style.display = 'inline-block';
    document.getElementById('enter-button').nextSibling.remove();
}

startCountdown();

// ... (rest of your code) ...

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

function fetchRecentWinners() {
    $.ajax({
        type: "POST",
        url: "get_recent_winners.php",
        data: { giveawayId: giveawayId },
        success: function(data) {
            $("#recent-winners-list").html(data);
        }
    });
}

fetchRecentWinners();
setInterval(fetchRecentWinners, 10000);

// Establish a connection with the SSE server
</script>
</body>
</html>