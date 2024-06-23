<?php
require_once 'config.php';
require_once 'upgiveaway.php';
$conn = db_connect();

$_SESSION['giveawayId'] = getGiveawayId($conn);

if (!isset($_SESSION['user_token'])) {
    header("Location: index.php");
    die();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ?");
$stmt->bind_param("s", $_SESSION['user_token']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $userinfo = $result->fetch_assoc();
    $unique_id = $userinfo['unique_id'];
} else {
    header("Location: index.php");
    die();
}

function getUpdatedEntryPoints($conn, $unique_id, $giveawayId) {
    $sql = "SELECT points FROM giveaway WHERE unique_id = '$unique_id' AND giveaway_id = '$giveawayId'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['points'];
}
function getUserRank($conn, $unique_id) {
    $sql = "SELECT rank FROM users WHERE unique_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $unique_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['rank'];
    }
    return 'Coin Collector'; // Default rank if not found
}

$userRank = getUserRank($conn, $unique_id);

$roblox_avatar_url = $userinfo['roblox_avatar_url'];

function isUserEligible($conn, $unique_id) {
    $currentDate = date("Y-m-d H:i:s");
    $twoDateAgo = date("Y-m-d H:i:s", strtotime("-2 days"));

    $sql = "SELECT COUNT(*) as count FROM offers WHERE unique_id = '$unique_id' AND timestamp BETWEEN '$twoDateAgo' AND '$currentDate'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $count = $row['count'];

    return $count >= 2;
}


$currentWinner = isset($_SESSION['current_winner']) ? $_SESSION['current_winner'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BLOXCASHON</title>
    <link rel="stylesheet" href="giveaway.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    <p>Your entry points: <span class="entry-points"><span id="user-entry-points">
        <?php
        $giveawayId = $_SESSION['giveawayId'];
        $sql = "SELECT points FROM giveaway WHERE unique_id = '$unique_id' AND giveaway_id = '$giveawayId'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        echo ($row) ? $row['points'] : '0';
        ?>
    </span> entry point(s)</span></p>
    <p>Your winning chance: <span class="winning-chance"><span id="user-winning-chance">0</span>%</span></p>
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
    document.querySelector('.enter-btn').addEventListener('click', function() {
    let isEligible = <?php echo isUserEligible($conn, $unique_id) ? 'true' : 'false'; ?>;
    if (!isEligible) {
        Swal.fire({
            title: 'Not Eligible',
            text: "You need to have completed two offers/surveys within the past two days to join the giveaway. If you have done so recently, refresh the page and try again!",
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    $.ajax({
        type: 'POST',
        url: 'upgiveaway.php',
        data: { 
            enterGiveaway: true, 
            uniqueId: '<?php echo $unique_id; ?>', 
            giveawayId: '<?php echo $_SESSION['giveawayId']; ?>',
            rank: '<?php echo $userRank; ?>'  // Use the fetched rank
        },
        success: function(data) {
        try {
            let response = JSON.parse(data);
            if (response.status === 'success') {
                Swal.fire({
                    title: 'Success!',
                    text: "You have successfully entered the giveaway! " + response.message,
                    icon: 'success',
                    confirmButtonText: 'Great!'
                });
            } else if (response.status === 'already_entered') {
                Swal.fire({
                    title: 'Already Entered',
                    text: "You have already entered the giveaway.",
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: "An error occurred: " + response.message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        } catch (e) {
            console.error("Failed to parse JSON:", data);
            Swal.fire({
                title: 'Unexpected Error',
                text: "An unexpected error occurred. Please try again later.",
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    },
    error: function(jqXHR, textStatus, errorThrown) {
        console.error("AJAX error:", textStatus, errorThrown);
        Swal.fire({
            title: 'Error',
            text: "An error occurred. Please try again later.",
            icon: 'error',
            confirmButtonText: 'OK'
        });
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
            updateWinningChance(); // Call this after updating total entries
        }
    });
}

    $(document).ready(function() {
        updateEntriesCount();
        setInterval(updateEntriesCount, 10000);
    });


    let countdownInterval;
    let currentHour = new Date().getUTCHours();
    let startTime = new Date().setUTCHours(currentHour, 0, 0, 0); // Set start time to the current hour (e.g., 12:00:00 UTC)
    let currentTime = new Date().getTime();
    let timeRemaining;
    let minutes;
    let seconds;
    let hasReloaded = false;

    function startCountdown() {
    countdownInterval = setInterval(function() {
        currentTime = new Date().getTime();
        timeRemaining = startTime + 3600000 - currentTime;

        minutes = Math.floor((timeRemaining % 3600000) / 60000);
        seconds = Math.floor((timeRemaining % 60000) / 1000);

        if (minutes === 3 && seconds === 0) {
            // Select the winner
            $.ajax({
                type: 'POST',
                url: 'select_winner.php',
                data: { giveawayId: giveawayId },
                success: function(response) {
                    console.log("Winner selected:", response);
                }
            });
        }

        if (minutes === 5 && seconds === 0) {
            document.getElementById('time-heading').textContent = "Time to next round";
            document.getElementById('enter-button').style.display = 'none';
            document.getElementById('enter-button').insertAdjacentText('afterend', 'Picking round winner');
        }

        if (minutes === 2 && seconds === 0) {
            // Display the winner
            $.ajax({
                type: 'GET',
                url: 'get_current_winner.php',
                success: function(winner) {
                    if (winner) {
                        let winnerMessage = `Congratulations to ${winner} for winning this round's giveaway!🥳`;
                        document.getElementById('enter-button').nextSibling.textContent = winnerMessage;
                    }
                }
            });
        }

        document.querySelector('#time-remaining').innerHTML = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

        if (timeRemaining <= 0) {
            clearInterval(countdownInterval);
            reloadOnce();
            resetGiveaway();
            startTime = new Date().setUTCHours(currentHour + 1, 0, 0, 0);
            currentHour = (currentHour + 1) % 24;
            startCountdown();
        }
    }, 1000);
}

function reloadOnce() {
    if (!sessionStorage.getItem('reloaded')) {
        sessionStorage.setItem('reloaded', true);
        location.reload();
    } else {
        sessionStorage.removeItem('reloaded');
    }
}

function resetGiveaway() {
    // Reset entries count
    $("#entries-count").text('0');

    // Reset giveaway content
    document.getElementById('time-heading').textContent = "Time remaining";
    document.getElementById('enter-button').style.display = 'inline-block';
    if (document.getElementById('enter-button').nextSibling.nodeType === Node.TEXT_NODE) {
        document.getElementById('enter-button').nextSibling.remove();
    }

    // Clear the current winner
    $.ajax({
        url: 'clear_winner.php',
        type: 'POST',
        success: function(response) {
            console.log('Current winner cleared');
        }
    });
}
$(document).ready(function(){
    startCountdown();
});
</script>

<script>
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

    $(document).ready(function() {
        fetchRecentEntries();
        setInterval(fetchRecentEntries, 10000);
    });

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

    $(document).ready(function() {
        fetchHighestPoints();
        setInterval(fetchHighestPoints, 10000);
    });

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

    $(document).ready(function() {
        fetchRecentWinners();
        setInterval(fetchRecentWinners, 10000);
    });
</script>
<script>
function updateEntryPoints() {
    $.ajax({
        type: "POST",
        url: "get_entry_points.php",
        data: {
            unique_id: '<?php echo $unique_id; ?>',
            giveawayId: '<?php echo $_SESSION['giveawayId']; ?>'
        },
        success: function(data) {
            $("#user-entry-points").text(data);
            updateWinningChance(); // Call this after updating entry points
        }
    });
}

$(document).ready(function() {
    // Update entry points every 5 seconds
    setInterval(updateEntryPoints, 5000);
});
function logToServer(message) {
    $.ajax({
        type: 'POST',
        url: 'log_to_server.php',
        data: { message: message },
        success: function(response) {
            console.log("Logged to server:", message);
        },
        error: function(xhr, status, error) {
            console.error("Failed to log to server:", status, error);
        }
    });
}

// Then use this function in your existing code:
if (minutes === 3 && seconds === 0) {
    logToServer("Attempting to select winner");
    $.ajax({
        type: 'POST',
        url: 'select_winner.php',
        data: { giveawayId: giveawayId },
        success: function(response) {
            logToServer("Winner selection response: " + response);
        },
        error: function(xhr, status, error) {
            logToServer("Error selecting winner: " + status + " " + error);
        }
    });
}

function updateWinningChance() {
    let userPoints = parseInt($("#user-entry-points").text());
    
    $.ajax({
        type: "POST",
        url: "upgiveaway.php",
        data: { 
            getTotalPoints: true,
            giveawayId: giveawayId
        },
        success: function(totalPoints) {
            totalPoints = parseInt(totalPoints);
            if (totalPoints > 0) {
                let winningChance = (userPoints / totalPoints) * 100;
                $("#user-winning-chance").text(winningChance.toFixed(2));
            } else {
                $("#user-winning-chance").text("0.00");
            }
        }
    });
}

$(document).ready(function() {
    // Update entry points and winning chance every 5 seconds
    setInterval(function() {
        updateEntryPoints();
        updateWinningChance();
    }, 5000);
});
</script>
<script>
let giveawayState = 'accepting_entries';
let serverTimeOffset = 0;

function syncWithServer() {
    $.ajax({
        url: 'get_server_state.php',
        success: function(data) {
            giveawayState = data.state;
            serverTimeOffset = new Date().getTime() - data.serverTime;
            updateUI();
        }
    });
}

function getServerTime() {
    return new Date().getTime() - serverTimeOffset;
}

function updateUI() {
    let remainingTime = calculateRemainingTime();
    $('#time-remaining').text(formatTime(remainingTime));

    switch(giveawayState) {
        case 'accepting_entries':
            $('#enter-button').show();
            $('#status-message').text('');
            break;
        case 'selecting_winner':
            $('#enter-button').hide();
            $('#status-message').text('Picking round winner');
            break;
        case 'displaying_winner':
            $('#enter-button').hide();
            $.ajax({
                url: 'get_current_winner.php',
                success: function(winner) {
                    $('#status-message').text(`Congratulations to ${winner} for winning this round's giveaway!🥳`);
                }
            });
            break;
    }
}

// Call this function periodically
setInterval(syncWithServer, 5000);

// Initial sync
syncWithServer();
</script>
<script>
function updateUI(data) {
    $('#time-remaining').text(formatTime(data.timeRemaining));

    if (data.state === 'accepting_entries' && data.canEnter) {
        $('#enter-button').show();
        $('#status-message').text('');
    } else if (data.state === 'selecting_winner') {
        $('#enter-button').hide();
        $('#status-message').text('Picking round winner');
    } else if (data.state === 'displaying_winner') {
        $('#enter-button').hide();
        $('#status-message').text(`Congratulations to ${data.currentWinner} for winning this round's giveaway!🥳`);
    }

    // Update giveaway ID if it has changed
    if (giveawayId !== data.giveawayId) {
        giveawayId = data.giveawayId;
        // Reset UI elements as needed for new giveaway
    }
}

function syncWithServer() {
    $.ajax({
        url: 'get_server_state.php',
        success: function(data) {
            updateUI(data);
        }
    });
}

// Call this function periodically
setInterval(syncWithServer, 5000);

// Initial sync
syncWithServer();
</script>
</body>
</html>