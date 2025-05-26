<?php
session_start();
// Security: only admin can perform actions
if (!isset($_SESSION['admin_user'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'fail', 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request method']);
    exit;
}

// Get POST data
$productIndex = isset($_POST['product_index']) ? (int)$_POST['product_index'] : -1;
$categoryName = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';
$categoryDescription = isset($_POST['category_description']) ? trim($_POST['category_description']) : '';

if ($productIndex < 0 || empty($categoryName)) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'fail', 'message' => 'Missing required parameters']);
    exit;
}

// Load products XML
$productsFile = __DIR__ . '/../data.xml';
if (!file_exists($productsFile)) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'fail', 'message' => 'Products data file not found']);
    exit;
}

$xml = simplexml_load_file($productsFile);
if (!$xml) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'fail', 'message' => 'Failed to load products data']);
    exit;
}

// Get unified products array
$allProducts = [];
if (isset($xml->products->product)) {
    foreach ($xml->products->product as $prod) {
        $allProducts[] = $prod;
    }
}
if (isset($xml->featuredProducts->product)) {
    foreach ($xml->featuredProducts->product as $fprod) {
        $allProducts[] = $fprod;
    }
}

// Check if product index is valid
if ($productIndex >= 0 && $productIndex < count($allProducts)) {
    $product = $allProducts[$productIndex];
    
    // Remove existing category if present
    if (isset($product->category)) {
        unset($product->category);
    }
    
    // Add new category
    $category = $product->addChild('category');
    $category->addChild('name', htmlspecialchars($categoryName));
    $category->addChild('description', htmlspecialchars($categoryDescription));
    
    // Save changes
    if ($xml->asXML($productsFile)) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Category assigned successfully'
        ]);
        exit;
    }
}

// If we get here, something went wrong
header('Content-Type: application/json');
echo json_encode(['status' => 'fail', 'message' => 'Failed to assign category']);
exit;
