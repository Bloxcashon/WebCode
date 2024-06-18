<?php
if (isset($_GET['gamepass_id'])) {
  $gamepassId = $_GET['gamepass_id'];
  $command = "python rb_bot.py " . escapeshellarg($gamepassId);
  shell_exec($command);
  echo "Python script executed with gamepass ID: $gamepassId";
}
?>