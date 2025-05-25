<?php
session_start();
// Security: only admin can perform
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_index'])) {
    $index = (int) $_POST['order_index'];
    $ordersFile = __DIR__ . '/../database/orders.xml';

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
