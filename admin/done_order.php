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
    }    // Ensure database directory exists
    $databaseDir = __DIR__ . '/../database';
    if (!is_dir($databaseDir)) {
        mkdir($databaseDir, 0755, true);
    }
    
    $ordersFile = $databaseDir . '/orders.xml';
    $soldFile = $databaseDir . '/sold.xml';

    // Check if orders file exists, create it if needed
    if (!file_exists($ordersFile)) {
        // Create empty orders XML
        $newOrdersXml = new DOMDocument('1.0');
        $newOrdersXml->formatOutput = true;
        $rootElement = $newOrdersXml->createElement('orders');
        $newOrdersXml->appendChild($rootElement);
        $newOrdersXml->save($ordersFile);
        
        die('Orders file created, but no orders found. Please add orders before trying to process them.');
    }
    
    if (!file_exists($soldFile)) {
        // Create sold.xml if it doesn't exist
        $newSoldXml = new DOMDocument('1.0');
        $newSoldXml->formatOutput = true;
        $rootElement = $newSoldXml->createElement('orders');
        $newSoldXml->appendChild($rootElement);
        $newSoldXml->save($soldFile);
    }

    // Load orders
    $ordersDoc = new DOMDocument();
    $ordersDoc->preserveWhiteSpace = false;
    $ordersDoc->formatOutput = true;
    if ($ordersDoc->load($ordersFile)) {
        $ordersRoot = $ordersDoc->documentElement;
        $rootName = $ordersRoot->nodeName; // Get the actual root element name
        
        $xpath = new DOMXPath($ordersDoc);
        // Use the correct root element name from the XML
        $orderNodes = $xpath->query('/' . $rootName . '/order');
        
        // Debug information
        if (!$orderNodes || $orderNodes->length === 0) {
            die('No orders found in XML. XPath: /' . $rootName . '/order');
        }
        
        if ($index >= $orderNodes->length) {
            die('Order index out of range. Requested: ' . $index . ', Available: ' . $orderNodes->length);
        }
        
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
            
            // Remove from orders
            $orderNode->parentNode->removeChild($orderNode);
            $ordersDoc->save($ordersFile);
            
            // Success! Redirect
            header('Location: admin_dashboard.php?success=order_completed');
            exit;
        } else {
            die('Failed to load sold.xml');
        }
    } else {
        die('Failed to load orders.xml');
    }
} else {
    die('Invalid request method or missing order index.');
}
