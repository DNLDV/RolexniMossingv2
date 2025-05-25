<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user'])) {
    exit("Not logged in");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit("Invalid request method");
}

// Read cart items from JSON request
$inputData = json_decode(file_get_contents('php://input'), true);
$cartItems = $inputData['items'] ?? [];

$ordersFile = 'database/orders.xml';

// 1) Load or create a DOMDocument for orders.xml
$dom = new DOMDocument('1.0', 'UTF-8');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;

// Check if orders.xml exists and has valid XML first
if (file_exists($ordersFile) && filesize($ordersFile) > 0) {
    // Attempt to load existing XML
    if (@$dom->load($ordersFile) === false) {
        // If loading fails due to invalid XML, create an empty <orders> root
        $orders = $dom->createElement('orders');
        $dom->appendChild($orders);
    }
} else {
    // orders.xml missing or empty, create new <orders> root
    $orders = $dom->createElement('orders');
    $dom->appendChild($orders);
}

// 2) Ensure we have a valid <orders> root element
$root = $dom->getElementsByTagName('orders')->item(0);
if (!$root) {
    $root = $dom->createElement('orders');
    $dom->appendChild($root);
}

// 3) Create new <order> element with <username>
$orderElement = $dom->createElement('order');
$usernameElement = $dom->createElement('username', $_SESSION['user']['name']);
$orderElement->appendChild($usernameElement);

// 4) Loop through items and create <item> elements
foreach ($cartItems as $item) {
    $itemNode = $dom->createElement('item');

    $titleNode = $dom->createElement('title', $item['title']);
    $priceNode = $dom->createElement('price', $item['price']);

    $itemNode->appendChild($titleNode);
    $itemNode->appendChild($priceNode);

    $orderElement->appendChild($itemNode);
}

// 5) Append this new <order> to the root
$root->appendChild($orderElement);

// 6) Save changes back to orders.xml
$dom->save($ordersFile);

// 7) Return success message
echo "success";
?>