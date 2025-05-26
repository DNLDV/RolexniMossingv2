<?php
session_start();
// Security: only admin can perform
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_index'])) {
    // Make sure we're working with a clean integer value
    $index = is_numeric($_POST['order_index']) ? intval($_POST['order_index']) : -1;
    if ($index < 0) {
        die('Invalid order index format. Received: "' . htmlspecialchars($_POST['order_index']) . '"');
    }
    
    // Ensure database directory exists
    $databaseDir = __DIR__ . '/../database';
    if (!is_dir($databaseDir)) {
        mkdir($databaseDir, 0755, true);
    }
    
    $ordersFile = $databaseDir . '/orders.xml';

    $doc = new DOMDocument();
    $doc->preserveWhiteSpace = false;
    $doc->formatOutput = true;
    if ($doc->load($ordersFile)) {
        $xpath = new DOMXPath($doc);
        $orderNodes = $xpath->query('/orders/order');

        if ($orderNodes->length > $index) {
            $toRemove = $orderNodes->item($index);
            $toRemove->parentNode->removeChild($toRemove);
            $doc->save($ordersFile);
        }
    }
}

// Redirect back to dashboard
header('Location: admin_dashboard.php');
exit;
