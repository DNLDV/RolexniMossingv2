<?php
// This file handles AJAX product deletion
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('error_reporting', E_ALL);

session_start();
// Security: redirect if not logged in as admin
if (!isset($_SESSION['admin_user'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'fail', 'message' => 'Unauthorized']);
    exit;
}

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request method']);
    exit;
}

// Get the product title and section
$productTitle = isset($_POST['title']) ? $_POST['title'] : '';
$section = isset($_POST['section']) ? $_POST['section'] : '';
$productIndex = isset($_POST['product_index']) ? (int)$_POST['product_index'] : -1;

if (empty($productTitle) && $productIndex < 0) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'fail', 'message' => 'No product identifier provided']);
    exit;
}

// Load products XML
$productsFile = __DIR__ . '/../data.xml';
if (!file_exists($productsFile)) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'fail', 'message' => 'Products data file not found']);
    exit;
}

if ($productIndex >= 0) {
    // Delete by index (more reliable)
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

    if ($productIndex < count($allProducts)) {
        $product = $allProducts[$productIndex];
        $domProduct = dom_import_simplexml($product);
        $domParent = $domProduct->parentNode;

        if ($domParent) {
            $domParent->removeChild($domProduct);
            
            // Save the updated XML
            if ($xml->asXML($productsFile)) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Product deleted successfully'
                ]);
                exit;
            }
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode(['status' => 'fail', 'message' => 'Could not delete product']);
    exit;
} else {
    // Delete by title and section (original method)
    $xml = new DOMDocument('1.0');
    $xml->load($productsFile);
    $xpath = new DOMXPath($xml);

    // Find the product with matching title in the specified section
    if ($section === 'featuredProducts') {
        $query = "/shop/featuredProducts/product[title='{$productTitle}']";
    } else {
        $query = "/shop/products/product[title='{$productTitle}']";
    }

    $productNodes = $xpath->query($query);
    if ($productNodes->length > 0) {
        $productNode = $productNodes->item(0);
        $productNode->parentNode->removeChild($productNode);
        
        // Save the updated XML
        if ($xml->save($productsFile)) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success', 
                'message' => 'Product deleted successfully'
            ]);
            exit;
        }
    }

    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'fail', 
        'message' => 'Product not found or could not be deleted'
    ]);
    exit;
}
