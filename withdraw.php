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

// Fetch user balance
$balance_sql = "SELECT balance FROM userpoints WHERE unique_id = '$unique_id'";
$balance_result = mysqli_query($conn, $balance_sql);
$balance_row = mysqli_fetch_assoc($balance_result);
$balance = $balance_row['balance'];

// Fetch user's games
$user_id = $userinfo['user_id'];
$url = "https://games.roblox.com/v2/users/$user_id/games?accessFilter=2&limit=10&sortOrder=Asc";
$response = file_get_contents($url);
$games = json_decode($response, true);


if (isset($_POST['amount'])) {
    $amount = $_POST['amount'];

    if ($balance >= $amount) {
        // Calculate withdrawal amount with 30% Roblox fee
        $withdrawal_amount = $amount + ($amount * 0.30);

        // Display games container
        $display_games = true;

        // Display alert box to user
        echo '<script>alert("You will receive '.($amount).' R$ after deducting 30% Roblox fee. Your new balance will be '.($balance - $withdrawal_amount).' R$.");</script>';

        // Update user balance
        $new_balance = $balance - $withdrawal_amount;
        $balance_update_sql = "UPDATE userpoints SET balance = '$new_balance' WHERE unique_id = '$unique_id'";
        mysqli_query($conn, $balance_update_sql);

        // Display success message
        echo '<script>alert("Withdrawal successful!");</script>';

    } else {
        echo "Insufficient balance!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BLOXCASHON</title>
    <link rel="stylesheet" href="withdraw.css">
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
        <p>Your Roblox ID is:<?php echo $userinfo['user_id'];?>
      </p>

      <a class="button-29" id="balance-button">
    <?php
        echo $balance. '$';
   ?>
</a>
</p>
<a href="logout.php" class="button-28">Log Out</a>
    </div>

    <div class="emethods-box">
        <div class="referral-container">
            <h2>Withdraw</h2>
            <form action="" method="post">
                <label for="amount">Amount (R$)</label>
                <input type="number" name="amount" id="amount" min="0" step="0.01" required>
                <button type="submit">Withdraw</button>
            </form>

            <?php if (isset($display_games)):?>
                <div class="your-games">
                    <h2>Your Games:</h2>
                    <ul>
                        <?php foreach ($games['data'] as $game):?>
                            <li>
                                <form action="" method="post">
                                    <input type="hidden" name="game_id" value="<?php echo $game['id'];?>">
                                    <button type="submit">Choose <?php echo $game['name'];?></button>
                                </form>
                            </li>
                        <?php endforeach;?>
                    </ul>
                    <?php if (count($games['data']) == 0):?>
                        <p>Kindly create a game under your profile to continue.</p>
                    <?php endif;?>
                </div>
            <?php endif;?>
        </div>
    </div>
</section>

<script src="menu.js"></script>
</body>
</html>