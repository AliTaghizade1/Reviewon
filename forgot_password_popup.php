<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="modal" style="display:block;">
    <div class="modal-content">
      <span class="close" onclick="window.history.back()">&times;</span>
      <h2>Forgot Password</h2>
      <form id="forgotForm" action="password_reset_request.php" method="POST">
        <div class="form-group">
          <label for="email">Your email</label>
          <input type="email" id="email" name="email" placeholder="you@example.com" required>
        </div>
        <button type="submit" class="btn-primary">Send reset link</button>
      </form>
    </div>
  </div>
</body>
</html>

