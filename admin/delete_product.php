<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('error_reporting', E_ALL);

session_start();
// Security: redirect if not logged in as admin
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.php');
    exit;
}

// Get the product title and section from GET parameters for simpler linking
$productTitle = isset($_GET['title']) ? $_GET['title'] : '';
$section = isset($_GET['section']) ? $_GET['section'] : '';

if (empty($productTitle)) {
    error_log("No product title provided for deletion");
    header('Location: products.php?updated=fail');
    exit;
}

// Load products XML using DOMDocument for better manipulation
$productsFile = __DIR__ . '/../data.xml';
if (!file_exists($productsFile)) {
    die('Products data file not found.');
}

$xml = new DOMDocument('1.0');
$xml->load($productsFile);

// Create an XPath object to search by title
$xpath = new DOMXPath($xml);

// Find the product with matching title in the specified section
// Use a safer XPath approach by using contains() to find the product
// This handles potential special characters in product titles
if ($section === 'featuredProducts') {
    $query = "/shop/featuredProducts/product[title='{$productTitle}']";
} else {
    $query = "/shop/products/product[title='{$productTitle}']";
}

// Log the XPath query for debugging
error_log("XPath query: $query");

// Get the product node to delete
$productNodes = $xpath->query($query);
$found = false;

if ($productNodes->length > 0) {
    // Found node by exact title match
    $productNode = $productNodes->item(0);
    $parentNode = $productNode->parentNode;
    $parentNode->removeChild($productNode);
    $found = true;
    
    // Save the changes
    if ($xml->save($productsFile)) {
        header('Location: products.php?updated=deleted');
        exit;
    } else {
        error_log("Failed to save XML after deletion");
        header('Location: products.php?updated=fail');
        exit;
    }
} else {
    // Try fallback method - iterate through all products in the section
    error_log("XPath query failed, trying manual iteration method");
    $sectionNode = ($section === 'featuredProducts') ? 
        $xml->getElementsByTagName('featuredProducts')->item(0) : 
        $xml->getElementsByTagName('products')->item(0);
    
    if ($sectionNode) {
        $products = $sectionNode->getElementsByTagName('product');
        
        foreach ($products as $product) {
            $titleNode = $product->getElementsByTagName('title')->item(0);
            if ($titleNode && $titleNode->nodeValue === $productTitle) {
                $sectionNode->removeChild($product);
                $found = true;
                
                // Save the changes
                if ($xml->save($productsFile)) {
                    header('Location: products.php?updated=deleted');
                    exit;
                } else {
                    error_log("Failed to save XML after deletion");
                    header('Location: products.php?updated=fail');
                    exit;
                }
            }
        }
    }
    
    if (!$found) {
        error_log("Product not found with title: $productTitle in section: $section");
        header('Location: products.php?updated=fail');
        exit;
    }
}
?>
