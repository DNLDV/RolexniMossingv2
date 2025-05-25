<?php
// filepath: c:\xampp\htdocs\RolexniMossingv2\admin\admin_dashboard.php
session_start();

// Security: redirect if not logged in as admin
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.php');
    exit;
}

// Load orders.xml
$ordersFile = __DIR__ . '/../database/orders.xml';
if (!file_exists($ordersFile)) {
    die('Orders file not found.');
}
$orders = simplexml_load_file($ordersFile);
if (!$orders) {
    die('Failed to load orders.xml');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
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
      <form id="logout-form" action="logout.php" method="post" style="display:none;"></form>
    </nav>
  </aside>
  <div class="main-content">
    <!-- Dashboard Header -->
    <header class="admin-header">
      <h1>Admin Dashboard</h1>
      <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_user']); ?>!</p>
      <form action="logout.php" method="post" style="display:inline;">
        <button type="submit" class="btn-logout">Log Out</button>
      </form>
    </header>

    <!-- Orders Table -->
    <section class="admin-container">
      <table class="order-table">
        <thead>
          <tr>
            <th>Username</th>
            <th>Items</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders->order as $idx => $order): ?>
          <tr>
            <td><?php echo htmlspecialchars($order->username); ?></td>
            <td>
              <?php foreach ($order->item as $item): ?>
              <div class="order-item">
                <strong><?php echo htmlspecialchars($item->title); ?></strong><br>
                ₱<?php echo htmlspecialchars($item->price); ?>
              </div>
              <?php endforeach; ?>
            </td>
            <td>
              <form action="delete_order.php" method="post" style="display:inline;">
                <input type="hidden" name="order_index" value="<?php echo $idx; ?>">
                <button type="submit" class="btn-delete">Delete</button>
              </form>
              <form action="done_order.php" method="post" style="display:inline;">
                <input type="hidden" name="order_index" value="<?php echo $idx; ?>">
                <button type="submit" class="btn-done">Done</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </div>
</div>
</body>
</html>
