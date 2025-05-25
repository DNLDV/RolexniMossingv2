<?php
session_start();
include '..\connection.php'; // Database connection


$isAdminLoggedIn = isset($_SESSION['admin_user']);

// If already logged-in, skip login
if ($isAdminLoggedIn) {
    header('Location: admin_dashboard.php');
    exit;
}

// If form is submitted, process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Fetch hashed password for the given username
    $stmt = $conn->prepare("SELECT password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_user'] = $username;
            header('Location: admin_dashboard.php');
            exit;
        } elseif ($row['password'] === $password) {
            // Legacy password stored in plain text, rehash it for security
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE admins SET password = ? WHERE username = ?");
            $updateStmt->bind_param("ss", $newHash, $username);
            $updateStmt->execute();
            $_SESSION['admin_user'] = $username;
            header('Location: admin_dashboard.php');
            exit;
        } else {
            $error = 'Invalid admin credentials';
        }
    } else {
        $error = 'Invalid admin credentials';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <div class="form-container">
    <form class="form" action="index.php" method="POST">
      <h1 class="admin-heading">Admin Login</h1>
      <?php if (!empty($error)): ?>
        <p style="color:red;"><?php echo $error; ?></p>
      <?php endif; ?>

      <div class="flex-column">
        <label>Username</label>
      </div>
      <div class="inputForm">
        <input type="text" class="input" placeholder="Enter your Username" name="username" required>
      </div>

      <div class="flex-column">
        <label>Password</label>
      </div>
      <div class="inputForm">
        <input type="password" class="input" placeholder="Enter your Password" name="password" required>
      </div>

      <div class="flex-row">
        <div>
          <input type="checkbox">
          <label>Remember me</label>
        </div>
        <span class="span">Forgot password?</span>
      </div>
      <button class="button-submit" type="submit">Sign In</button>
    </form>
  </div>
</body>
</html>