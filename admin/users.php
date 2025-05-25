<?php
session_start();
// Security: redirect if not logged in as admin
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.php');
    exit;
}

include __DIR__ . '/../connection.php'; // Database connection

// Fetch all users
$sql = "SELECT id, fullname, email, created_at FROM users";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Users Management</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="dashboard-wrapper">
  <aside class="sidebar">
    <h2>Admin Panel</h2>
    <nav>
      <a href="admin_dashboard.php">Dashboard</a>
      <a href="users.php">Users</a>
      <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
    </nav>
    <form id="logout-form" action="logout.php" method="post" style="display:none;"></form>
  </aside>
  <div class="main-content">
    <header class="admin-header">
      <h1>User Accounts</h1>
      <form action="logout.php" method="post" style="display:inline;">
        <button type="submit" class="btn-logout">Log Out</button>
      </form>
    </header>
    <?php if (isset($_GET['delete'])): ?>
    <div class="alert <?php echo $_GET['delete'] === 'success' ? 'alert-success' : 'alert-error'; ?>">
      <?php echo $_GET['delete'] === 'success' ? 'User deleted successfully.' : 'Error: Incorrect admin password.'; ?>
    </div>
    <?php endif; ?>
    <section class="admin-container">
      <table class="user-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['id']); ?></td>
              <td><?php echo htmlspecialchars($row['fullname']); ?></td>
              <td><?php echo htmlspecialchars($row['email']); ?></td>
              <td><?php echo htmlspecialchars($row['created_at']); ?></td>
             <td>
                <form method="post" action="delete_user.php" style="display:inline;" onsubmit="return promptAdminPassword(this)">
                  <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                  <input type="hidden" name="admin_password" value="">
                  <button type="submit" class="btn-delete-user">Delete</button>
                </form>
              </td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="5">No users found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </div>
</div>
<script>
  function promptAdminPassword(form) {
    var pwd = prompt('Enter your admin password to confirm deletion:');
    if (!pwd) return false;
    form.admin_password.value = pwd;
    return true;
  }
</script>
</body>
</html>
