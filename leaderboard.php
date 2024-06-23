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

// Fetch user's total earnings and rank
$sql = "SELECT emethod + refbonus + sclink AS total_earned, total FROM userpoints WHERE unique_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $unique_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_earned = $row['total_earned'];
$total_points = $row['total'];

// Fetch top 3 users for weekly leaderboard


// Calculate time remaining until Sunday midnight UTC
$now = new DateTime('now', new DateTimeZone('UTC'));
$end_of_week = new DateTime('saturday this week', new DateTimeZone('UTC'));
$end_of_week->setTime(23, 59, 59);

// If it's already past Saturday 23:59:59, set to next Saturday
if ($now > $end_of_week) {
    $end_of_week->modify('+7 days');
}

$time_remaining = $now->diff($end_of_week);

if ($now->format('w') == 0 && $now->format('H') == 0 && $now->format('i') == 0) {
    // It's Sunday at 00:00 UTC, log the new week start
    error_log("New week started, resetting leaderboard");
}


function ordinal($number) {
    $suffix = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number % 100) <= 13))
        return $number. 'th';
    else
        return $number. $suffix[$number % 10];
}
$weekly_sql = "
SELECT 
    u.roblox_username, 
    u.roblox_avatar_url, 
    SUM(e.earnings) AS points,
    DATE(DATE_SUB(CURRENT_DATE(), INTERVAL WEEKDAY(CURRENT_DATE()) DAY)) AS week_start
FROM 
    users u
JOIN 
    earned e ON u.unique_id = e.unique_id
WHERE 
    e.time >= DATE(DATE_SUB(CURRENT_DATE(), INTERVAL (DAYOFWEEK(CURRENT_DATE()) - 1) DAY))
    AND e.time < DATE(DATE_ADD(DATE(DATE_SUB(CURRENT_DATE(), INTERVAL (DAYOFWEEK(CURRENT_DATE()) - 1) DAY)), INTERVAL 7 DAY))
    AND e.type IN ('referral', 'survey', 'offer')
GROUP BY 
    u.unique_id, u.roblox_username, u.roblox_avatar_url
ORDER BY 
    points DESC
LIMIT 20
";

$weekly_result = mysqli_query($conn, $weekly_sql);

// Fetch top 20 users for lifetime leaderboard
$lifetime_sql = "SELECT u.roblox_username, u.roblox_avatar_url, up.total AS points 
                 FROM users u 
                 JOIN userpoints up ON u.unique_id = up.unique_id 
                 ORDER BY up.total DESC 
                 LIMIT 20";
$lifetime_result = mysqli_query($conn, $lifetime_sql);

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BLOXCASHON - Leaderboard</title>
    <link rel="stylesheet" href="leaderboard.css">
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
                <p>Your Roblox ID is: <?php echo $userinfo['user_id'];?></p>
                <a class="button-29" id="balance-button">
                    <?php
                    $balance_sql = "SELECT balance FROM userpoints WHERE unique_id = '$unique_id'";
                    $balance_result = mysqli_query($conn, $balance_sql);
                    $balance_row = mysqli_fetch_assoc($balance_result);
                    echo $balance_row['balance']. ' R$';
                    ?>
                </a>
                <a href="logout.php" class="button-28">Log Out</a>
            </div>

            <div class="emethods-box">
            <div class="leaderboard-box">
                <h2>Leaderboard</h2>
                <div class="leaderboard-timer" id="leaderboard-timer">
    <?php echo $time_remaining->days . 'D, ' . $time_remaining->h . 'H, ' . $time_remaining->i . 'M, ' . $time_remaining->s . 'S'; ?>
</div>
                <div class="leaderboard-tabs">
                    <button class="tab-active" data-tab="weekly">WEEKLY</button>
                    <button data-tab="lifetime">LIFETIME</button>
                    <button data-tab="race">RACE</button>
                </div>
                <div class="leaderboard-entries weekly">
    <div class="top-ranks">
        <?php
        $rank = 1;
        $weekly_result = mysqli_query($conn, $weekly_sql);
        while ($row = mysqli_fetch_assoc($weekly_result)) {
            if ($rank <= 3) {
                $rank_class = $rank == 1 ? 'first-place' : ($rank == 2 ? 'second-place' : 'third-place');
                $reward = $rank == 1 ? 300 : ($rank == 2 ? 200 : 100);
                ?>
                <div class="leaderboard-entry <?php echo $rank_class; ?>">
                    <img src="<?php echo htmlspecialchars($row['roblox_avatar_url']); ?>" alt="<?php echo htmlspecialchars($row['roblox_username']); ?> avatar" class="avatar">
                    <span class="username"><?php echo htmlspecialchars($row['roblox_username']); ?></span>
                    <span class="earned">Earned <?php echo number_format($row['points']); ?> R$</span>
                    <span class="reward">+<?php echo $reward; ?> R$</span>
                </div>
                <?php
            } else {
                break;
            }
            $rank++;
        }
        ?>
    </div>
    <div class="leaderboard-list">
        <?php
        mysqli_data_seek($weekly_result, 3);
        while ($row = mysqli_fetch_assoc($weekly_result)) {
            ?>
            <div class="leaderboard-list-item">
                <img src="<?php echo htmlspecialchars($row['roblox_avatar_url']); ?>" alt="<?php echo htmlspecialchars($row['roblox_username']); ?> avatar" class="avatar-small">
                <span class="username"><?php echo htmlspecialchars($row['roblox_username']); ?></span>
                <span class="earned"><?php echo number_format($row['points']); ?> R$</span>
                <span class="reward">+50 R$</span>
                <div class="rank">#<?php echo $rank; ?></div>
            </div>
            <?php
            $rank++;
        }
        ?>
    </div>
</div>

<div class="leaderboard-entries lifetime" style="display: none;">
    <div class="top-ranks">
        <?php
        $rank = 1;
        mysqli_data_seek($lifetime_result, 0);
        while ($row = mysqli_fetch_assoc($lifetime_result)) {
            if ($rank <= 3) {
                $rank_class = $rank == 1 ? 'first-place' : ($rank == 2 ? 'second-place' : 'third-place');
                $reward = $rank == 1 ? 300 : ($rank == 2 ? 200 : 100);
                ?>
                <div class="leaderboard-entry <?php echo $rank_class; ?>">
                    <img src="<?php echo htmlspecialchars($row['roblox_avatar_url']); ?>" alt="<?php echo htmlspecialchars($row['roblox_username']); ?> avatar" class="avatar">
                    <span class="username"><?php echo htmlspecialchars($row['roblox_username']); ?></span>
                    <span class="earned">Earned <?php echo number_format($row['points']); ?> R$</span>
                    <span class="reward">+<?php echo $reward; ?> R$</span>
                    <div class="rank-icon"><?php echo ordinal($rank); ?></div>
                </div>
                <?php
            } else {
                break;
            }
            $rank++;
        }
        ?>
    </div>
    <div class="leaderboard-list">
        <?php
        mysqli_data_seek($lifetime_result, 3);
        while ($row = mysqli_fetch_assoc($lifetime_result)) {
            ?>
            <div class="leaderboard-list-item">
                <img src="<?php echo htmlspecialchars($row['roblox_avatar_url']); ?>" alt="<?php echo htmlspecialchars($row['roblox_username']); ?> avatar" class="avatar-small">
                <span class="username"><?php echo htmlspecialchars($row['roblox_username']); ?></span>
                <span class="earned"><?php echo number_format($row['points']); ?> R$</span>
                <span class="reward">+50 R$</span>
                <div class="rank">#<?php echo $rank; ?></div>
            </div>
            <?php
            $rank++;
        }
        ?>
    </div>
</div>
                <div class="leaderboard-entries race" style="display: none;">
                    <p>Race leaderboard coming soon!</p>
                </div>
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
    </div>
    </section>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.leaderboard-tabs button');
    const entries = document.querySelectorAll('.leaderboard-entries');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const tabName = tab.getAttribute('data-tab');
            
            tabs.forEach(t => t.classList.remove('tab-active'));
            tab.classList.add('tab-active');

            entries.forEach(entry => {
                entry.style.display = entry.classList.contains(tabName) ? 'flex' : 'none';
            });
        });
    });

    function updateTimer() {
    const now = new Date();
    const end = new Date();
    end.setUTCDate(end.getUTCDate() + ((6 - end.getUTCDay() + 7) % 7));
    end.setUTCHours(23, 59, 59, 999);
    
    const diff = end - now;
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    document.getElementById('leaderboard-timer').textContent = 
        `${days}D, ${hours}H, ${minutes}M, ${seconds}S`;
}
        updateTimer();
        setInterval(updateTimer, 1000);
    });
    </script>
</body>
</html>