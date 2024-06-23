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
// Fetch offers completed today
$date = new DateTime('now', new DateTimeZone('UTC'));
$today = $date->format('Y-m-d');

$sql = "SELECT COUNT(*) as count FROM offers WHERE unique_id = ? AND DATE(timestamp) = ? AND amount >= 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $unique_id, $today);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$offers_today = $row['count'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BLOXCASHON</title>
    <link rel="stylesheet" href="tasks.css">
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
        <h1>Tasks</h1>
        <h2>Complete Tasks Daily to earn extra Free R$</h2>
        <div class="notice" style="background-color: orangered; display: inline-block; padding: 5px 10px; border-radius:30px; border:2px solid #fff; margin-bottom: 10px;">
    <div class="timer" id="timer" style="display: inline-block;"></div>
    <span style="font-size: 18px; color: #fff; margin-left: 10px; display: inline-block;">UNTIL TASKS RESET</span>
    </div>
            <div class="rewards-container">
            <div class="task-box">
    <h3>Complete 1 Offer</h3>
    <i class="ri-information-2-fill"> Only Counts 10 R$ Offers and above</i>
    <div class="progress">
        <div class="progress-bar" id="progress-bar-1" style="width: <?= ($offers_today >= 1) ? '100%' : ($offers_today / 1 * 100) . '%' ?>"></div>
    </div>
    <span class="progress-indicator" id="task-progress-1"><!-- Progress value will be inserted here --></span>
    <button class="button">Earn 3 R$</button>
</div>
<div class="task-box">
    <h3>Complete 3 Offers</h3>
    <i class="ri-information-2-fill"> Only Counts 10 R$ Offers and above</i>
    <div class="progress">
        <div class="progress-bar" id="progress-bar-3" style="width: <?= ($offers_today >= 3) ? '100%' : ($offers_today / 3 * 100) . '%' ?>"></div>
    </div>
    <span class="progress-indicator" id="task-progress-3"><!-- Progress value will be inserted here --></span>
    <button class="button">Earn 7 R$</button>
</div>
<div class="task-box">
    <h3>Complete 5 Offers</h3>
    <i class="ri-information-2-fill"> Only Counts 10 R$ Offers and above</i>
    <div class="progress">
        <div class="progress-bar" id="progress-bar-5" style="width: <?= ($offers_today >= 5) ? '100%' : ($offers_today / 5 * 100) . '%' ?>"></div>
    </div>
    <span class="progress-indicator" id="task-progress-5"><!-- Progress value will be inserted here --></span>
    <button class="button">Earn 10 R$</button>
</div>
<div class="task-box">
    <h3>Complete 10 Offers</h3>
    <i class="ri-information-2-fill"> Only Counts 10 R$ Offers and above</i>
    <div class="progress">
        <div class="progress-bar" id="progress-bar-10" style="width: <?= ($offers_today >= 10) ? '100%' : ($offers_today / 10 * 100) . '%' ?>"></div>
    </div>
    <span class="progress-indicator" id="task-progress-10"><!-- Progress value will be inserted here --></span>
    <button class="button">Earn 15 R$</button>
</div>
<div class="task-box">
    <h3>Complete 15 Offers</h3>
    <i class="ri-information-2-fill"> Only Counts 10 R$ Offers and above</i>
    <div class="progress">
        <div class="progress-bar" id="progress-bar-15" style="width: <?= ($offers_today >= 15) ? '100%' : ($offers_today / 15 * 100) . '%' ?>"></div>
    </div>
    <span class="progress-indicator" id="task-progress-15"><!-- Progress value will be inserted here --></span>
    <button class="button">Earn 20 R$</button>
</div>
<div class="task-box">
    <h3>Complete 20 Offers</h3>
    <i class="ri-information-2-fill"> Only Counts 10 R$ Offers and above</i>
    <div class="progress">
        <div class="progress-bar" id="progress-bar-20" style="width: <?= ($offers_today >= 20) ? '100%' : ($offers_today / 20 * 100) . '%' ?>"></div>
    </div>
    <span class="progress-indicator" id="task-progress-20"><!-- Progress value will be inserted here --></span>
    <button class="button">Earn 30 R$</button>
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
  var offersToday = <?php echo json_encode($offers_today); ?>;

var taskProgressElements = document.getElementsByClassName('progress-indicator');

for (var i = 0; i < taskProgressElements.length; i++) {
    var taskProgressElement = taskProgressElements[i];
    var taskId = taskProgressElement.id.replace('task-progress-', ''); // Extract the task ID from the element ID
    var targetOffers = parseInt(taskId, 10); // Convert the task ID to an integer (base 10)
    
    // Check if targetOffers is a valid number
    if (!isNaN(targetOffers)) {
        taskProgressElement.textContent = offersToday + '/' + targetOffers;
    } else {
        // Handle the case where the element ID doesn't match the expected pattern
        taskProgressElement.textContent = 'Invalid ID';
    }
}
</script>

    <script>
        function updateTimer() {
    const now = new Date();
    const endOfDay = new Date();
    endOfDay.setUTCHours(23, 59, 59, 999); // Set to end of current UTC day

    const diff = endOfDay - now;

    if (diff > 0) {
        const hours = Math.floor(diff / 1000 / 60 / 60);
        const minutes = Math.floor((diff / 1000 / 60) % 60);
        const seconds = Math.floor((diff / 1000) % 60);

        document.getElementById('timer').textContent = 
            `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    } else {
        document.getElementById('timer').textContent = "00:00:00";
    }
}

setInterval(updateTimer, 1000);
updateTimer(); // Call immediately to avoid initial delay
    </script>

</body>
</html>




