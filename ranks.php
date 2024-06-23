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

// Define rank thresholds
$ranks = [
    'Coin Collector' => 0,
    'robux-rookie' => 1500,
    'robux-ranger' => 7500,
    'robux-renegade' => 19395,
    'robux-royalty' => 48515,
    'robux-legend' => 108825
];

// Determine current rank
$current_rank = 'Coin Collector';
foreach ($ranks as $rank => $threshold) {
    if ($total_earned >= $threshold) {
        $current_rank = $rank;
    } else {
        break;
    }
}

// Get next rank
$rank_keys = array_keys($ranks);
$current_rank_index = array_search($current_rank, $rank_keys);
$next_rank = ($current_rank_index < count($rank_keys) - 1) ? $rank_keys[$current_rank_index + 1] : $current_rank;

// Calculate progress to next rank
$current_threshold = $ranks[$current_rank];
$next_threshold = $ranks[$next_rank];
$progress = ($total_earned - $current_threshold) / ($next_threshold - $current_threshold) * 100;
$progress = min(100, max(0, $progress)); // Ensure progress is between 0 and 100

// Get leaderboard position
$sql = "SELECT COUNT(*) + 1 AS leaderboard_position FROM userpoints WHERE total > ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('d', $total_points);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$leaderboard_position = $row['leaderboard_position'];

// Update user's rank in the database
$sql = "UPDATE users SET rank = ? WHERE unique_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $current_rank, $unique_id);
$stmt->execute();

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BLOXCASHON</title>
    <link rel="stylesheet" href="ranks.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@200..700&family=Poetsen+One&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks & Challenges</title>
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
    <div class="ranks-container">
    <div class="rank-and-stats">
    <div class="current-rank-progress">
    <h2>Current Rank Progress</h2>
    <div class="user-rank-card <?php echo strtolower(str_replace(' ', '-', $current_rank)); ?>">
        <h3><?php echo $current_rank; ?></h3>
        <p><?php
            switch($current_rank) {
                case 'Coin Collector':
                    echo "Come on, don't be shy🫣You are on your way to make passive income 💰";
                    break;
                case 'robux-rookie':
                    echo "You're breaking new ground! Your passive income is taking off!💫";
                    break;
                case 'robux-ranger':
                    echo "Your treasury is overflowing! You're a Robux mastermind!😤";
                    break;
                case 'robux-renegade':
                    echo "You're collecting more than just coins - you're collecting success!💼";
                    break;
                case 'robux-royalty':
                    echo "You're part of the Robux elite! Your earnings are fit for a king!👑";
                    break;
                case 'robux-legend':
                    echo "You're a robux-legend in your own right! Your Robux legacy will live on!🦁";
                    break;
            }
        ?></p>
        <div class="progress">
            <div class="progress-bar" style="width: <?php echo $progress; ?>%"></div>
        </div>
        <p>Earn <?php echo $total_earned; ?> / <?php echo $ranks[$next_rank]; ?> Robux</p>
        <p class="passive-income">Passive income: <?php
            switch($current_rank) {
                case 'Coin Collector':
                    echo "0 R$ Daily";
                    break;
                case 'robux-rookie':
                    echo "4 R$ Daily";
                    break;
                case 'robux-ranger':
                    echo "8 R$ Daily";
                    break;
                case 'robux-renegade':
                    echo "13 R$ Daily";
                    break;
                case 'robux-royalty':
                    echo "15 R$ Daily";
                    break;
                case 'robux-legend':
                    echo "20 R$ Daily";
                    break;
            }
        ?></p>
    </div>
</div>
        <div class="sta">
        <h2>Statistics</h2>
        <div class="statistics">
            <p>Current Rank: <?php echo $current_rank; ?></p>
            <p>Next Rank: <?php echo $next_rank; ?></p>
            <p>Current Level: <?php echo $current_rank_index; ?></p>
            <p>Next Level: <?php echo $current_rank_index + 1; ?></p>
            <p>Leaderboard Position: #<?php echo $leaderboard_position; ?></p>
            <p>Experience: <?php echo $total_earned; ?></p>
        </div>
        </div>
    </div>
    <div class="rank-cards">
<?php 
$rank_keys = array_keys($ranks);
for ($i = 1; $i < count($rank_keys); $i++): 
    $rank = $rank_keys[$i];
    $threshold = $ranks[$rank];
    $prev_threshold = $ranks[$rank_keys[$i-1]];
    
    // Calculate progress for this specific rank
    if ($total_earned >= $threshold) {
        $rank_progress = 100; // Set to 100% if threshold is met or exceeded
    } else {
        $rank_progress = min(100, max(0, ($total_earned - $prev_threshold) / ($threshold - $prev_threshold) * 100));
    }
?>
<div class="rank-card <?php echo strtolower($rank); ?>">
    <h3><?php echo $rank; ?></h3>
    <p><?php
        switch($rank) {
            case 'robux-rookie':
                echo "You're breaking new ground! Your passive income is taking off!💫";
                break;
            case 'robux-ranger':
                echo "Your treasury is overflowing! You're a Robux mastermind!😤";
                break;
            case 'robux-renegade':
                echo "You're collecting more than just coins - you're collecting success!💼";
                break;
            case 'robux-royalty':
                echo "You're part of the Robux elite! Your earnings are fit for a king!👑";
                break;
            case 'robux-legend':
                echo "You're a robux-legend in your own right! Your Robux legacy will live on!🦁";
                break;
        }
    ?></p>
    <div class="progress">
        <div class="progress-bar" style="width: <?php echo $rank_progress; ?>%"></div>
    </div>
    <p>Earn <?php echo number_format(min($total_earned, $threshold)); ?> / <?php echo number_format($threshold); ?> Robux</p>

        <div class="perks" id="perks">
            <?php
            switch($rank) {
                case 'robux-rookie':
                    echo '<i class="ri-shield-check-fill"><span style="font-family: Impact, serif;">4 R$ daily</span></i>';
                    echo '<i class="ri-shield-check-fill"><span style="font-family: Impact, serif;">2 Free Entry Points every Giveaway!</span></i>';
                    break;
                case 'robux-ranger':
                    echo '<i class="ri-shield-check-fill"><span style="font-family: Impact, serif;">8 R$ daily</span></i>';
                    echo '<i class="ri-shield-check-fill"><span style="font-family: Impact, serif;">4 Free Entry Points every Giveaway!</span></i>';
                    break;
                case 'robux-renegade':
                    echo '<i class="ri-shield-check-fill"><span style="font-family: Impact, serif;">13 R$ daily</span></i>';
                    echo '<i class="ri-shield-check-fill"><span style="font-family: Impact, serif;">8 Free Entry Points every Giveaway!</span></i>';
                    break;
                case 'robux-royalty':
                    echo '<i class="ri-shield-check-fill"><span style="font-family: Impact, serif;">15 R$ daily</span></i>';
                    echo '<i class="ri-shield-check-fill"><span style="font-family: Impact, serif;">10 Free Entry Points every Giveaway!</span></i>';
                    echo '<i class="ri-shield-check-fill"><span style="font-family: Impact, serif;">Royalty Discord Role!</span></i>';
                    break;
                case 'robux-legend':
                    echo '<i class="ri-shield-check-fill"><span style="font-family: Impact, serif;">20 R$ daily</span></i>';
                    echo '<i class="ri-shield-check-fill"><span style="font-family: Impact, serif;">12 Free Entry Points every Giveaway!</span></i>';
                    echo '<i class="ri-shield-check-fill"><span style="font-family: Impact, serif;">Legend Discord Role!</span></i>';
                    break;
            }
            ?>
        </div>
    </div>
    <?php endfor; ?>
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

</body>
</html>