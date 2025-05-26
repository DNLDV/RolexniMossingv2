<?php
session_start();
include 'connection.php';
// Validate token
$token = $_GET['token'] ?? '';
if (!$token) {
    $_SESSION['error'] = 'Invalid or missing reset token.';
    header('Location: index.php');
    exit();
}
// Check token in DB
$stmt = $conn->prepare('SELECT id, reset_expires FROM users WHERE reset_token = ?');
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Invalid or expired reset link.';
    header('Location: index.php');
    exit();
}
$user = $result->fetch_assoc();
if (strtotime($user['reset_expires']) < time()) {
    $_SESSION['error'] = 'Reset link has expired.';
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Password - PFRolex</title>
  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet" href="css/toast.css" />
  <style>
    .forgot-modal { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #f5f5f5; }
    .forgot-modal__content { background: #fff; padding: 2rem; border-radius: 10px; max-width: 400px; width: 90%; text-align: center; box-shadow: 0 4px 16px hsla(0, 0%, 0%, 0.1); }
    .forgot-modal__logo { width: 120px; margin-bottom: 10px; }
    .forgot-form input { width: 100%; padding: 10px; margin: 8px 0; border-radius: 6px; border: 1px solid #ccc; }
    .forgot-form .forgot-btn { width: 100%; padding: 10px; margin-top: 10px; background: #e60023; color: white; border: none; border-radius: 6px; cursor: pointer; }
  </style>
</head>
<body>
<div class="forgot-modal">
  <div class="forgot-modal__content">
    <img src="assets/img/logo.png" alt="Logo" class="forgot-modal__logo">
    <h2>Reset Password</h2>
    <form class="forgot-form" action="update_password.php" method="post">
      <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>" />
      <input type="password" name="password" placeholder="New password" required />
      <input type="password" name="confirm_password" placeholder="Confirm new password" required />
      <button type="submit" class="forgot-btn">Update Password</button>
    </form>
    <p class="signup-text"><a href="index.php">Back to Login</a></p>
  </div>
</div>
</body>
</html>
