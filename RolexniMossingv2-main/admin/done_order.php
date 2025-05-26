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
    $soldFile = __DIR__ . '/../database/sold.xml';

    // Load orders
    $ordersDoc = new DOMDocument();
    $ordersDoc->preserveWhiteSpace = false;
    $ordersDoc->formatOutput = true;
    if ($ordersDoc->load($ordersFile)) {
        $xpath = new DOMXPath($ordersDoc);
        $orderNodes = $xpath->query('/orders/order');

        if ($orderNodes->length > $index) {
            $orderNode = $orderNodes->item($index);

            // Load sold
            $soldDoc = new DOMDocument();
            $soldDoc->preserveWhiteSpace = false;
            $soldDoc->formatOutput = true;
            if ($soldDoc->load($soldFile)) {
                $soldRoot = $soldDoc->documentElement;
                // Import the node into soldDoc
                $imported = $soldDoc->importNode($orderNode, true);
                $soldRoot->appendChild($imported);
                $soldDoc->save($soldFile);
            }

            // Remove from orders
            $orderNode->parentNode->removeChild($orderNode);
            $ordersDoc->save($ordersFile);
        }
    }
}

// Redirect back to dashboard
header('Location: admin_dashboard.php');
exit;
