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

$week_start = date('Y-m-d', strtotime('last sunday midnight'));

// Calculate user's earnings for the current week
$sql = "SELECT SUM(emethod + refbonus + sclink) AS weekly_earnings FROM userpoints WHERE unique_id = ? AND created_at >= ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $unique_id, $week_start);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$weekly_earnings = $row['weekly_earnings'] ?? 0;

// Calculate number of free spins
$free_spins = floor($weekly_earnings / 50);

// Check if user has a record for this week
$sql = "SELECT * FROM wheelspins WHERE unique_id = ? AND week_start = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $unique_id, $week_start);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0 && $free_spins > 0) {
    // Insert new record
    $sql = "INSERT INTO wheelspins (unique_id, number, week_start) VALUES (?, 0, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $unique_id, $week_start);
    $stmt->execute();
}

// Get current number of spins used
$sql = "SELECT number FROM wheelspins WHERE unique_id = ? AND week_start = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $unique_id, $week_start);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$spins_used = $row ? $row['number'] : 0;

$spins_left = max(0, $free_spins - $spins_used);

// Function to update spins count
function updateSpinsCount($conn, $unique_id, $week_start) {
    $sql = "UPDATE wheelspins SET number = number + 1 WHERE unique_id = ? AND week_start = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $unique_id, $week_start);
    $stmt->execute();
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BLOXCASHON</title>
    <link rel="stylesheet" href="wheel.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    <div class="wheel-container">
    <div class="rwrapper">
        <div class="main">
            <h1>Spin the Wheel to win R$</h1>
            <h2>You need to have completed offers worth R$ 100 in the past week to get a free spin!</h2>
            <?php if ($spins_left > 0): ?>
    <h4>You have <?php echo $spins_left; ?> free spin<?php echo $spins_left > 1 ? 's' : ''; ?>!</h4>
<?php elseif ($free_spins > 0): ?>
    <h4>You have used up your free spins! Earn 50R$ more this week to get another free spin</h4>
<?php else: ?>
    <h4>You don't have any free spin. Earn 50R$ this week to get a free spin</h4>
<?php endif; ?>
            <div class="onecontainer">
                <div class="wheel-container">
                <canvas id="wheel" class="cube"></canvas>
  <div class="top"></div>
                <button id="spin-btn">Spin</button>
                <img class="rimg" src="https://cdn-icons-png.flaticon.com/128/9590/9590006.png" alt="spinner-arrow" />
</div>
            <div id="final-value">
                <p>Click the Spin Button to Start!</p>
            </div>
            <button id="refresh-btn">Refresh Wheel</button>
        </div>
    </div>

    <!-- Chart JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <!-- Chart JS Plugin for displaying text over chart -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.1.0/chartjs-plugin-datalabels.min.js"></script>
    <!-- Script JS -->
    <script>
   const wheel = document.getElementById("wheel");
const spinBtn = document.getElementById("spin-btn");
const refreshBtn = document.getElementById("refresh-btn");
const finalValue = document.getElementById("final-value");

refreshBtn.style.display = 'none'; // Hide refresh button on initial load

const generateSections = () => [
    { label: `${Math.floor(Math.random() * 5) + 1}R$`, probability: 0.55 },
    { label: "No Reward💣", probability: 0.40625 },
    { label: `${Math.floor(Math.random() * 11) + 10}R$`, probability: 0.025 },
    { label: `${Math.floor(Math.random() * 71) + 30}R$`, probability: 0.0125 },
    { label: `${Math.floor(Math.random() * 251) + 150}R$`, probability: 0.006 },
    { label: `${Math.floor(Math.random() * 801) + 1000}R$`, probability: 0.00025 }
];

let sections = generateSections();

const createGradient = (ctx, chartArea, color1, color2) => {
    const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
    gradient.addColorStop(0, color1);
    gradient.addColorStop(1, color2);
    return gradient;
};

let myChart = new Chart(wheel, {
    plugins: [ChartDataLabels],
    type: "pie",
    data: {
        labels: sections.map(section => section.label),
        datasets: [{
            backgroundColor: (context) => {
                const chart = context.chart;
                const { ctx, chartArea } = chart;
                if (!chartArea) {
                    return null;
                }
                return [
                    createGradient(ctx, chartArea, '#1565C0', '#b92b27'),
                    createGradient(ctx, chartArea, '#ee9ca7', '#ffdde1'),
                    createGradient(ctx, chartArea, '#A5FECB', '#20BDFF'),
                    createGradient(ctx, chartArea, '#6FB1FC', '#4364F7'),
                    createGradient(ctx, chartArea, '#FFE000', '#799F0C'),
                    createGradient(ctx, chartArea, '#00416a', '#799f0c', '#ffe000'),
                ];
            },
            data: new Array(sections.length).fill(1),
        }],
    },
    options: {
        responsive: true,
        animation: { duration: 0 },
        plugins: {
            tooltip: false,
            legend: { display: false },
            datalabels: {
                color: "#ffffff",
                formatter: (_, context) => context.chart.data.labels[context.dataIndex],
                font: { size: 16, weight: 'bold' },
                anchor: 'end',
                align: 'start',
                offset: 5,
                rotation: (context) => {
                    const angle = context.chart.getDatasetMeta(0).data[context.dataIndex].startAngle +
                        (context.chart.getDatasetMeta(0).data[context.dataIndex].endAngle -
                            context.chart.getDatasetMeta(0).data[context.dataIndex].startAngle) / 2;
                    let degrees = angle * 180 / Math.PI;
                    return degrees > 90 && degrees < 270 ? degrees - 180 : degrees;
                }
            },
        },
    },
});

const getRandomSection = () => {
    const random = Math.random();
    let cumulativeProbability = 0;
    for (const section of sections) {
        cumulativeProbability += section.probability;
        if (random < cumulativeProbability) {
            return section.label;
        }
    }
    return sections[sections.length - 1].label; // Fallback to last section
};

const getRotationForSection = (sectionLabel) => {
    const sectionIndex = sections.findIndex(section => section.label === sectionLabel);
    const sectionAngle = 360 / sections.length;
    const middleOfSection = sectionIndex * sectionAngle + (sectionAngle / 2);
    return 90 - middleOfSection; // Adjusted for arrow on the right pointing left
};

let spinsLeft = <?php echo $spins_left; ?>;

function updateSpinsDisplay() {
    const spinsDisplay = document.querySelector('h4');
    if (spinsLeft > 0) {
        spinsDisplay.textContent = `You have ${spinsLeft} free spin${spinsLeft > 1 ? 's' : ''}!`;
    } else if (<?php echo $free_spins; ?> > 0) {
        spinsDisplay.textContent = "You have used up your free spins! Earn 50R$ more this week to get another free spin";
    } else {
        spinsDisplay.textContent = "You don't have any free spin. Earn 50R$ this week to get a free spin";
    }
    spinBtn.disabled = spinsLeft <= 0;
}
let spinAllowed = true;

const spin = () => {
    if (!spinAllowed) {
            Swal.fire({
                title: 'Please refresh the wheel first!',
                icon: 'error',
                background: '#ffe6e6',
                showConfirmButton: false,
  timer: 2500

            });
            return;
    }
    spinAllowed = false;
    if (spinsLeft <= 0) {
            Swal.fire({
                title: 'No more spins left!',
                icon: 'error',
                showConfirmButton: false,
  timer: 2500

            });
            return;
    }

    spinBtn.disabled = true;
    refreshBtn.style.display = 'none';
    finalValue.innerHTML = `<p>Spinning the wheel. Good luck!</p>`;

    const winner = getRandomSection();
    const finalRotation = getRotationForSection(winner);
    const totalDegrees = 360 * 5 + finalRotation;
    let currentRotation = 0;

    let rotationInterval = window.setInterval(() => {
        currentRotation += 10;
        myChart.options.rotation = currentRotation % 360;
        myChart.update();

        if (currentRotation >= Math.abs(totalDegrees)) {
            clearInterval(rotationInterval);
            myChart.options.rotation = finalRotation;
            myChart.update();
            Swal.fire({
  title: winner === 'No Reward💣'? 'Oops! You Ran Out of Luck This Time' : 'Congratulations!',
  icon: winner === 'No Reward💣'? 'warning' : 'success',
  html: winner === 'No Reward💣'? '<i>😭</i> You landed on nothing! Please try again' : `You have won ${winner}`,
  confirmButtonText: 'OK',
  customClass: {
    container: 'weet-alert-container', 
    title: 'weet-alert-title',
    icon: 'weet-alert-icon',
    htmlContainer: 'weet-alert-html',
    confirmButton: 'weet-alert-confirm-button'
  },
  background: winner === 'No Reward💣'? '#000' : '#34C759', // custom background color
  color: '#FFFFFF' // custom text color
});
            refreshBtn.style.display = 'inline-block';

            // Simulate server-side update of spinsLeft
            spinsLeft--; // Decrease spinsLeft by 1 after each spin
            updateSpinsDisplay(); // Update spins display after the spin

            spinBtn.disabled = spinsLeft <= 0;
        }
    }, 10);
    fetch('update_spins.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=spin'
    })
   .then(response => response.json())
        .then(data => {
            if (data.error) {
                Swal.fire({
                    title: data.error,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                spinBtn.disabled = false;
                spinAllowed = true; // Allow spinning again if there's an error
                return;
        }
    })
};

const refreshWheel = () => {
    sections = generateSections();
    myChart.data.labels = sections.map(section => section.label);
    myChart.update();
    spinBtn.disabled = false;
    refreshBtn.style.display = 'none'; // Hide refresh button after refresh
    finalValue.innerHTML = `<p>Wheel refreshed. Click Spin to start!</p>`;
    spinAllowed = true;
};


spinBtn.addEventListener("click", spin);
refreshBtn.addEventListener("click", refreshWheel);



    </script>

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