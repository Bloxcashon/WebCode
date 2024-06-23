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
    $user_id = $userinfo['user_id'];
    $roblox_username = $userinfo['roblox_username'];
    $roblox_avatar_url = $userinfo['roblox_avatar_url'];
} else {
    header("Location: index.php");
    die();
}
echo '<script>var uniqueId = "'.$unique_id.'"; var userId = "'.$user_id.'";</script>';

// Fetch user balance
$balance_sql = "SELECT balance FROM userpoints WHERE unique_id = '$unique_id'";
$balance_result = mysqli_query($conn, $balance_sql);
$balance_row = mysqli_fetch_assoc($balance_result);
$balance = $balance_row['balance'];

// Fetch user's games
$url = "https://games.roblox.com/v2/users/$user_id/games?accessFilter=2&limit=10&sortOrder=Asc";
$response = file_get_contents($url);
$games = json_decode($response, true);

if (isset($_POST['amount'])) {
    $amount = $_POST['amount'];

    if ($balance >= $amount) {
        // Calculate withdrawal amount with 10% Roblox fee
        $withdrawal_amount = $amount + ($amount * 1.3);

        // Display alert box to user
        echo '<script>alert("You will receive '. ($amount). '$ after deducting 10% Roblox fee. Your new balance will be '. ($balance - $withdrawal_amount). '$.");</script>';

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
    <link rel="stylesheet" href="withdraw1.css">
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
        <h1>Welcome, <?php echo $roblox_username;?>!</h1>
        <p>Your Roblox ID is:<?php echo $user_id;?>
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
            <div class="withdraw-steps" id="withdraw-steps">
                <div class="step">
                    <h3>Step 1</h3>
                    <input type="text" id="roblox-username" placeholder="Enter your RBLX username" value="<?php echo $roblox_username;?>" disabled>
                </div>
                <div class="step">
                    <h3>Step 2</h3>
                    <input type="number" id="withdraw-amount" placeholder="Enter how much R$ you want to withdraw" min="0" step="1" required>
                    <button id="withdraw-button">Withdraw</button>
                    <p>Minimum withdraw amount is 7 R$</p>
                    <a href="#">Want to see your history? Click here</a>
                    
                </div>
            </div>

            <div class="game-selection" id="game-selection" style="display: none;">
                <h2>Please select a game</h2>
                <ul>
                    <?php foreach ($games['data'] as $game):?>
                        <li>
                            <div class="game-card">
                                <img src="<?php echo $game['thumbnailUrl'];?>" alt="<?php echo $game['name'];?>">
                                <h3><?php echo $game['name'];?></h3>
                                <button class="select-game-btn" data-game-id="<?php echo $game['id'];?>">Select</button>
                            </div>
                        </li>
                    <?php endforeach;?>
                </ul>
                <?php if (count($games['data']) == 0):?>
                    <p>Kindly create a game under your profile to continue.</p>
                <?php endif;?>
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
  const withdrawButton = document.getElementById('withdraw-button');
  let selectedGameId = null;
  let withdrawAmount = null;

  withdrawButton.addEventListener('click', () => {
    withdrawAmount = document.getElementById('withdraw-amount').value;

    if (withdrawAmount >= 7) {
      const xhr = new XMLHttpRequest();
      xhr.open('GET', 'fetch_games.php', true);

      xhr.onload = function() {
        if (xhr.status === 200) {
          const games = JSON.parse(xhr.responseText);
          const withdrawSteps = document.getElementById('withdraw-steps');

          withdrawSteps.innerHTML = `
            <h2>Please select a game</h2>
            <ul>
          `;

          games.data.forEach(game => {
            withdrawSteps.innerHTML += `
              <li>
                <div class="game-card">
                  <img src="${game.thumbnailUrl}" alt="${game.name}">
                  <h3>${game.name}</h3>
                  <button class="select-game-btn" data-game-id="${game.id}">Select</button>
                </div>
              </li>
            `;
          });

          if (games.data.length === 0) {
            withdrawSteps.innerHTML += `
              <p>Kindly create a game under your profile to continue.</p>
            `;
          }
        }
      };

      xhr.send();
    } else {
      alert('Minimum withdraw amount is 7 R$');
    }
  });

  const withdrawSteps = document.getElementById('withdraw-steps');

  function showGamepassDetails(gameId) {
    const withdrawSteps = document.getElementById('withdraw-steps');
    const price = Math.ceil(withdrawAmount / (1 - 0.3));

    withdrawSteps.innerHTML = `
      <h3>Step 4 (Gamepass)</h3>
      <ol>
        <li>Click <a href="https://create.roblox.com/dashboard/creations/experiences/${gameId}/monetization/passes" target="_blank">this link</a> to create a gamepass for your selected game.</li>
        <li>Set the price of the gamepass to ${price}. </li>
        <li>After creating the gamepass, click the button below to complete your withdrawal.</li>
      </ol>
      <button id="check-gamepass-btn">Check Gamepass</button>
      <button id="confirm-withdrawal-btn">Confirm Withdrawal</button>
    `;

    const checkGamepassBtn = document.getElementById('check-gamepass-btn');
    const confirmWithdrawalBtn = document.getElementById('confirm-withdrawal-btn');

    checkGamepassBtn.addEventListener('click', () => {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `proxy.php?url=https://games.roblox.com/v1/games/${gameId}/game-passes?limit=10&sortOrder=1`, true);

    xhr.onload = function() {
        if (xhr.status === 200) {
            const gamepasses = JSON.parse(xhr.responseText);
            let gamepassId;

            for (const gamepass of gamepasses.data) {
                if (gamepass.price === price) {
                    gamepassId = gamepass.id;
                    break;
                }
            }

            if (gamepassId) {
                console.log(`Gamepass ID: ${gamepassId}`);
                // Store the gamepass ID for future use
                const xhrInsert = new XMLHttpRequest();
                xhrInsert.open('POST', 'insert_wstatus.php', true);
                xhrInsert.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                const data = `unique_id=${uniqueId}&user_id=${userId}&game_id=${gameId}&gmp_id=${gamepassId}&amount=${price}&status=pending`;
                xhrInsert.send(data);
            } else {
                alert('Gamepass not found');
            }
        }
    };

    xhr.send();
});
    confirmWithdrawalBtn.addEventListener('click', () => {
  const gamepassId = document.getElementById('gamepass-id').value;
  const xhr = new XMLHttpRequest();
  xhr.open('GET', 'execute_python_script.php?gamepass_id=' + gamepassId, true);
  xhr.send();
});
}

function showPrivateServerDetails(gameId) {
    const withdrawSteps = document.getElementById('withdraw-steps');
    const price = Math.ceil(withdrawAmount / (1 - 0.3));

    withdrawSteps.innerHTML = `
      <h3>Step 4 (Private Server)</h3>
      <ol>
        <li>Click <a href="https://create.roblox.com/dashboard/creations/experiences/${gameId}/access" target="_blank">this link</a> to create a private server for your selected game.</li>
        <li>Under the 'ACCESS' Tab, Click 'Allow Private Servers' then click 'Requires Robux' and set the price to ${price}.</li>
        <li>After creatingthe private server, click the button below to complete your withdrawal.</li>
      </ol>
      <button id="check-private-server-btn">Check Private Server</button>
      <button id="confirm-withdrawal-btn">Confirm Withdrawal</button>
    `;

    const checkPrivateServerBtn = document.getElementById('check-private-server-btn');
    const confirmWithdrawalBtn = document.getElementById('confirm-withdrawal-btn');

    checkPrivateServerBtn.addEventListener('click', () => {
      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'insert_wstatus.php', true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

      const data = `unique_id=${uniqueId}&user_id=${userId}&game_id=${gameId}&price=${price}&status=pending`;
      xhr.send(data);
    });

    confirmWithdrawalBtn.addEventListener('click', () => {
      // Handle Private Server creation logic here
      alert('Private Server created successfully!');
    });
}

  function showGamepassCreationStep(gameId) {
    const withdrawSteps = document.getElementById('withdraw-steps');

    withdrawSteps.innerHTML = `
      <h3>Step 3</h3>
      <p>Please choose an option to proceed with the withdrawal:</p>
      <div class="option-container">
        <button id="create-gamepass-btn">Create Gamepass</button>
        <button id="create-private-server-btn">Create Private Server</button>
      </div>
    `;

    const createGamepassBtn = document.getElementById('create-gamepass-btn');
    const createPrivateServerBtn = document.getElementById('create-private-server-btn');

    createGamepassBtn.addEventListener('click', () => {
      showGamepassDetails(gameId);
    });

    createPrivateServerBtn.addEventListener('click', () => {
      showPrivateServerDetails(gameId);
    });
  }
  

  withdrawSteps.addEventListener('click', (e) => {
    if (e.target.classList.contains('select-game-btn')) {
      selectedGameId = e.target.getAttribute('data-game-id');
      showGamepassCreationStep(selectedGameId);
    }
  });

  // Add the following script block

  function checkUpdates() {
  const xhr = new XMLHttpRequest();
  xhr.open('GET', 'check_updates.php?unique_id=' + uniqueId, true);

  xhr.onload = function() {
    if (xhr.status === 200) {
      const response = xhr.responseText;
      try {
        const updates = JSON.parse(response);
        if (updates.length > 0) {
          updates.forEach((update) => {
            if (update.type === 'fintxn') {
              console.log(`Success! You have received ${update.amount} R$. Kindly allow 3-5 days for it to reflect on your account. You can confirm the payout via this link https://www.roblox.com/transactions`);
            } else if (update.type === 'errtxn') {
              console.log(`Error ${update.error_code}: Your withdrawal could not be completed ${update.error_message}`);
            }
          });
        }
      } catch (e) {
        console.error('Invalid JSON response:', response);
      }
    }
  };

  xhr.send();
}

setInterval(checkUpdates, 5000); // Check for updates every 5 seconds

</script>