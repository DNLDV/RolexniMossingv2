<?php
// This file returns product details as JSON for the modal
header('Content-Type: application/json');

// Load XML data
$xml = simplexml_load_file("data.xml") or die("Error: Cannot load XML file");
$response = ['success' => false];

// Get the product ID (title) from the request
$productId = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($productId)) {
    $response['error'] = 'Product ID is required';
    echo json_encode($response);
    exit;
}

// Search for the product in both featured and regular products
$product = null;

// Check featured products
foreach ($xml->featuredProducts->product as $featuredProduct) {
    if (strtolower(trim((string)$featuredProduct->title)) === strtolower(trim($productId))) {
        $product = $featuredProduct;
        break;
    }
}

// Check regular products if not found in featured
if ($product === null) {
    foreach ($xml->products->product as $regularProduct) {
        if (strtolower(trim((string)$regularProduct->title)) === strtolower(trim($productId))) {
            $product = $regularProduct;
            break;
        }
    }
}

// Return product details or error
if ($product !== null) {
    $response = [
        'success' => true,
        'name' => (string)$product->title,
        'price' => (string)$product->price,
        'description' => (string)$product->description,
        'image' => (string)$product->image,
        'tag' => isset($product->tag) ? (string)$product->tag : ''
    ];
} else {
    $response['error'] = 'Product not found';
}

echo json_encode($response);
?>
