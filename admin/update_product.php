<?php
session_start();
// Security: redirect if not logged in as admin
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.php');
    exit;
}

// Load products XML
$productsFile = __DIR__ . '/../data.xml';
if (!file_exists($productsFile)) {
    die('Products data file not found.');
}

$xml = simplexml_load_file($productsFile);
if (!$xml) {
    die('Failed to load products data.');
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productIndex = isset($_POST['product_index']) ? (int)$_POST['product_index'] : -1;
    $action = $_POST['action'] ?? '';

    if ($productIndex >= 0 && $productIndex < count($allProducts)) {
        $product = $allProducts[$productIndex];
        
        if ($action === 'update') {
            // Update product details
            $product->title = $_POST['title'] ?? $product->title;
            $product->price = $_POST['price'] ?? $product->price;
            $product->tag = $_POST['tag'] ?? $product->tag;
            $product->quantity = $_POST['quantity'] ?? $product->quantity;
            $product->description = $_POST['description'] ?? $product->description;

            // Update category if provided
            if (!empty($_POST['category'])) {
                if (!isset($product->category)) {
                    $product->addChild('category');
                }
                $product->category->name = $_POST['category'];
                $product->category->description = $_POST['category_desc'] ?? '';
            }

            // Handle image upload if provided
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $targetDir = __DIR__ . '/../assets/img/products/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $imageFileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $targetFile = $targetDir . basename($_FILES['image']['name']);

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $product->image = 'assets/img/products/' . basename($_FILES['image']['name']);
                }
            }
        } elseif ($action === 'delete') {
            // Remove the product from its parent element
            $parent = $product->xpath('..')[0];
            unset($parent->product[array_search($product, iterator_to_array($parent->product))]);
        }        // Save changes
        if ($xml->asXML($productsFile)) {
            // For AJAX requests, return text response
            if (isset($_POST['description']) && !isset($_FILES['image'])) {
                echo 'success';
                exit;
            }
            // For form submissions, redirect
            header('Location: products.php?updated=' . ($action === 'update' ? 'success' : 'deleted'));
            exit;
        } else {
            if (isset($_POST['description']) && !isset($_FILES['image'])) {
                echo 'fail';
                exit;
            }
            header('Location: products.php?updated=fail');
            exit;
        }
    }
}

if (isset($_POST['description']) && !isset($_FILES['image'])) {
    echo 'fail';
    exit;
}
header('Location: products.php?updated=fail');
exit;