<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Forgot Password - PFRolex</title>
  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet" href="css/toast.css" />
  <style>
    .forgot-modal {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      background: #f5f5f5;
    }
    .forgot-modal__content {
      background: #fff;
      padding: 2rem;
      border-radius: 10px;
      max-width: 400px;
      width: 90%;
      text-align: center;
      box-shadow: 0 4px 16px hsla(0, 0%, 0%, 0.1);
    }
    .forgot-modal__logo {
      width: 120px;
      margin-bottom: 10px;
    }
    .forgot-form input {
      width: 100%;
      padding: 10px;
      margin: 8px 0;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    .forgot-form .forgot-btn {
      width: 100%;
      padding: 10px;
      margin-top: 10px;
      background: #e60023;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
  </style>
</head>
<body>
<div class="forgot-modal">
  <div class="forgot-modal__content">
    <img src="assets/img/logo.png" alt="Logo" class="forgot-modal__logo">
    <h2>Forgot Password</h2>
    <form class="forgot-form" action="send_reset.php" method="post">
      <input type="email" name="email" placeholder="Enter your email" required />
      <button type="submit" class="forgot-btn">Send Reset Link</button>
    </form>
    <p class="signup-text">Remembered? <a href="index.php" id="back-to-login">Back to Login</a></p>
  </div>
</div>
</body>
</html>
