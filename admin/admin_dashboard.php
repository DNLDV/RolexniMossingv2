<?php
// filepath: c:\xampp\htdocs\RolexniMossingv2\admin\admin_dashboard.php
session_start();

// Security: redirect if not logged in as admin
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.php');
    exit;
}

// Ensure database directory exists
$databaseDir = __DIR__ . '/../database';
if (!is_dir($databaseDir)) {
    mkdir($databaseDir, 0755, true);
}

// Load orders.xml
$ordersFile = $databaseDir . '/orders.xml';
if (!file_exists($ordersFile)) {
    // Create empty orders file
    $newOrdersXml = new SimpleXMLElement('<orders></orders>');
    $newOrdersXml->asXML($ordersFile);
    $orders = $newOrdersXml;
} else {
    $orders = simplexml_load_file($ordersFile);
    if (!$orders) {
        die('Failed to load orders.xml');
    }
}

// Load sold.xml
$soldFile = $databaseDir . '/sold.xml';
if (!file_exists($soldFile)) {
    // Create empty sold file
    $newSoldXml = new SimpleXMLElement('<orders></orders>');
    $newSoldXml->asXML($soldFile);
    $sold = $newSoldXml;
} else {
    $sold = simplexml_load_file($soldFile);
    if (!$sold) {
        die('Failed to load sold.xml');
    }
}

// Calculate total payments
$totalPayments = 0;
foreach ($sold->order as $order) {
    foreach ($order->item as $item) {
        $totalPayments += (float) $item->price;
    }
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
      <a href="products.php">Products</a>
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
        </thead>        <tbody>
          <?php $orderIndex = 0; ?>
          <?php foreach ($orders->order as $order): ?>
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
                <input type="hidden" name="order_index" value="<?php echo $orderIndex; ?>">
                <button type="submit" class="btn-delete">Delete</button>
              </form>              <form action="done_order.php" method="post" style="display:inline;">
                <input type="hidden" name="order_index" value="<?php echo $orderIndex; ?>">
                <button type="submit" class="btn-done">Done</button>
              </form>
            </td>
          </tr>
          <?php $orderIndex++; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>

    <!-- Completed Transactions Section -->
    <section class="admin-container">
      <h2>Completed Transactions</h2>
      <table class="order-table">
        <thead>
          <tr>
            <th>Username</th>
            <th>Items</th>
            <th>Total Price</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sold->order as $order): ?>
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
              ₱<?php 
              $orderTotal = 0;
              foreach ($order->item as $item) {
                  $orderTotal += (float) $item->price;
              }
              echo $orderTotal;
              ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <h3>Total Payments: ₱<?php echo $totalPayments; ?></h3>
    </section>
  </div>
</div>
</body>
</html>
