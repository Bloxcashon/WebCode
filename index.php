<?php
// Include the config.php file
require_once 'config.php';

// Initialize error message variable
$error_message = '';

// Check if the user is already logged in
if (isset($_SESSION['user_token'])) {
    header("Location: next.php");
    exit();
}

$referral_id = null;

if (isset($_GET['ref'])) {
    $referral_id = $_GET['ref'];
    // Store the referral ID in the session
    $_SESSION['referral_id'] = $referral_id;
}

// Handle Google Sign-In
if (isset($_GET['google-sign-in-nav']) || isset($_GET['google-sign-in-hero'])) {
    $authUrl = $client->createAuthUrl();
    header("Location: $authUrl");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BLOXCASHON</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <script src='https://www.hCaptcha.com/1/api.js' async defer></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@200..700&family=Poetsen+One&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</head>
<body>
<header class="nav">
    <a href="#" class="logo">BLOXCASHON</a>
    <ul class="navlist">
        <li><a href="#">Home</a></li>
        <li><a href="#">FAQ</a></li>
        <li><a href="#">Help</a></li>
        <li><a href="https://discord.gg/2r8adaxteD" target="_blank">Discord</a></li>
        <li><a href="https://www.youtube.com/channel/UC_tqQ8bcQ-PSdV9U0ZJPA1g?sub_confirmation=1" target="_blank">Youtube</a></li>
        <li><a href="https://x.com/bloxcashon" target="_blank">X</a></li>
        <li><a href="https://www.instagram.com/bloxcashon/" target="_blank">Instagram</a></li>
        <li><a href="https://www.tiktok.com/@bloxcashon" target="_blank">TikTok</a></li>
        <li><a href="https://www.facebook.com/bloxcashon" target="_blank">Facebook</a></li>
        <li><a class="nav-sign-up" id="google-sign-in-nav">Sign Up</a></li>
        <li><a href="#" onclick="openLoginModal()">Login</a></li>
    </ul>

    <div class="bx bx-menu" id="menu-icon"></div>
</header>
<div id="loginModal" class="login-modal">
    <div class="login-content">
        <div class="login-left">
            <h1>BLOXCASHON</h1>
            <h2>The #1 FREE Rewards Website</h2>
            <p class="bonus">🎁 New users get 2 R$</p>
        </div>
        <div class="login-right">
            <span class="close" onclick="closeLoginModal()">&times;</span>
            <h2>SIGN IN</h2>
            <form method="POST" action="">
    <label for="username">USERNAME</label>
    <input type="text" id="username" name="username" placeholder="Username" required>

    <div class="h-captcha" data-sitekey="1de85670-0cdb-46e7-aa7d-659d4d9c4e06"></div>

    <div class="terms-checkbox">
        <input type="checkbox" id="terms-agree" required>
        <label for="terms-agree">
            I have read and agree to the 
            <a href="privacy-policy" target="_blank" class="policy-link">Privacy Policy</a> and 
            <a href="terms" class="policy-link" target="_blank">Terms of Service</a>
        </label>
    </div>

    <button type="submit" name="login" class="enter-button">ENTER</button>
</form>
        </div>
    </div>
</div>
<section class="hero">
    <div class="hero-text">
        <h5>#1 Robux Earning Platform</h5>
        <h1>Earning R$ Oversimplified</h1>
        <h4>Securely earn R$ legitimately instead of being scammed by other clickbait websites</h4>
        <div class="text-container">
  <span class="constant-text">Earn through </span>
  <span class="variable-text"></span>
  <span class="cursor">|</span>
</div>
        <a href="#">Terms of Service</a>
        <a href="#" class="ctaa" id="google-sign-in-hero"><i class="ri-google-fill"><h2> Sign Up Now with Google</h2></i></a>
</div>
</div>

    <div class="hero-img">
        <img src="images/hero-img.png">
    </div>

</section>

<div class="icons">
    <a href="https://discord.gg/2r8adaxteD" target="_blank"><i class="ri-discord-fill"></i></a>
    <a href="https://www.youtube.com/channel/UC_tqQ8bcQ-PSdV9U0ZJPA1g?sub_confirmation=1" target="_blank"><i class="ri-youtube-fill"></i></a>
    <a href="https://x.com/bloxcashon" target="_blank"><i class="ri-twitter-x-line"></i></a>
    <a href="https://www.instagram.com/bloxcashon/" target="_blank"><i class="ri-instagram-fill"></i></a>
    <a href="https://www.facebook.com/bloxcashon" target="_blank"><i class="ri-facebook-fill"></i></a>
</div>

<section class="info" id="info">
    

    <div class="stat-items">
        <div class="stat-item">
            <div class="stat-dot"></div>
                <div class="stat-date">2024</div>
                <div class="stat-content">
                    <a href="#"><i class="ri-user-heart-fill"></i>4M+ Users</a>
                    <p>Have registered, used our website to earn R$ and LOVED IT! since 2023</p>
                </div>
            </div>
        

        <div class="stat-item">
            <div class="stat-dot"></div>
                <div class="stat-date">2024</div>
                <div class="stat-content">
                    <a href="#"><i class="ri-gift-fill"></i>115,421,210</a>
                    <p>R$ has been paid out to users of the platform since we started in 2023</p>
                </div>
            </div>
        

        <div class="stat-item">
            <div class="stat-dot"></div>
                <div class="stat-date">2024</div>
                <div class="stat-content">
                    <a href="#"><i class="ri-time-fill"></i>12 minutes!</a>
                    <p>The amount of time it takes to create an account, earn your first R$ and cash-out on our platform</p>
                </div>
            </div>
        
        
        
    </div>
</section>

<div class="scroll-down">
    <a href="#"><i class="ri-arrow-down-s-fill"></i></a>
</div>

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

<script src="https://unpkg.com/scrollreveal"></script>
<script>
  var referralId = '<?php echo isset($_SESSION['referral_id']) ? $_SESSION['referral_id'] : ''; ?>';
</script>
<script src="script.js"></script>
<script src="google-auth.js"></script>
<script>
const textContainer = document.querySelector('.text-container');
const constantText = 'Earn through ';
const variableTexts = [
  'surveys',
  'watching videos',
  'playing games',
  'downloading apps'
];

let currentTextIndex = 0;
let currentCharIndex = 0;

function typeText() {
  const variableText = variableTexts[currentTextIndex];
  const variableTextElement = textContainer.querySelector('.variable-text');

  if (currentCharIndex < variableText.length) {
    variableTextElement.textContent = variableText.substring(0, currentCharIndex + 1);
    currentCharIndex++;
    setTimeout(typeText, 250); // adjust the speed here
  } else {
    currentTextIndex = (currentTextIndex + 1) % variableTexts.length;
    currentCharIndex = 0;
    setTimeout(typeText, 500); // adjust the delay here
  }
}

function eraseText() {
  // Do nothing, just wait for the next text to be typed
}

typeText();
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var loginForm = document.querySelector('form');
    var termsCheckbox = document.getElementById('terms-agree');

    loginForm.addEventListener('submit', function(event) {
        event.preventDefault();

        if (!termsCheckbox.checked) {
            alert('Please agree to the Privacy Policy and Terms of Service before proceeding.');
            return;
        }

        var hcaptchaResponse = hcaptcha.getResponse();
        if (hcaptchaResponse.length == 0) {
            alert('Please complete the captcha.');
            return;
        }

        // If everything is valid, submit the form
        var formData = new FormData(loginForm);
        formData.append('login', '1');
        formData.append('h-captcha-response', hcaptchaResponse);

        fetch('login_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(text => {
            try {
                return JSON.parse(text);
            } catch (error) {
                console.error('Server response:', text);
                throw new Error('Invalid JSON response');
            }
        })
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error || "An unknown error occurred");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred. Please try again.");
        });
    });
});

function openLoginModal() {
    var loginModal = document.getElementById('loginModal');
    loginModal.style.display = 'flex';
}

function closeLoginModal() {
    var loginModal = document.getElementById('loginModal');
    loginModal.style.display = 'none';
}

window.onclick = function(event) {
    var loginModal = document.getElementById('loginModal');
    if (event.target == loginModal) {
        loginModal.style.display = 'none';
    }
}
</script>
</body>
</html>