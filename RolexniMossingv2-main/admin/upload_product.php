<?php
session_start();
// Only admin
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $tag = trim($_POST['tag'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $categoryName = trim($_POST['category'] ?? '');
    $categoryDesc = trim($_POST['category_desc'] ?? '');

    // Validate required fields
    if ($title && $price && $tag && $quantity && $desc && $categoryName && isset($_FILES['image'])) {
        // Handle image upload
        $uploadDir = __DIR__ . '/../assets/img/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $imageFile = $_FILES['image'];
        $targetPath = $uploadDir . basename($imageFile['name']);
        if (move_uploaded_file($imageFile['tmp_name'], $targetPath)) {
            // Relative path to store in XML
            $relativePath = 'assets/img/products/' . basename($imageFile['name']);
            
            // Load and update data.xml
            $file = __DIR__ . '/../data.xml';
            $doc = new DOMDocument();
            $doc->preserveWhiteSpace = false;
            $doc->formatOutput = true;
            if ($doc->load($file)) {
                $xpath = new DOMXPath($doc);
                $productsNode = $xpath->query('/shop/products')->item(0);
                if ($productsNode) {
                    // Create product element
                    $prodElem = $doc->createElement('product');
                    $prodElem->appendChild($doc->createElement('title', htmlspecialchars($title)));
                    $prodElem->appendChild($doc->createElement('price', htmlspecialchars($price)));
                    $prodElem->appendChild($doc->createElement('tag', htmlspecialchars($tag)));
                    $prodElem->appendChild($doc->createElement('image', htmlspecialchars($relativePath)));
                    $prodElem->appendChild($doc->createElement('quantity', htmlspecialchars($quantity)));
                    $prodElem->appendChild($doc->createElement('description', htmlspecialchars($desc)));
                    // Category element
                    $catElem = $doc->createElement('category');
                    $catElem->appendChild($doc->createElement('name', htmlspecialchars($categoryName)));
                    $catElem->appendChild($doc->createElement('description', htmlspecialchars($categoryDesc)));
                    $prodElem->appendChild($catElem);
                    $productsNode->appendChild($prodElem);
                    // Save XML
                    if ($doc->save($file)) {
                        header('Location: products.php?upload=success');
                        exit;
                    }
                }
            }
        }
    }
}
// On failure
header('Location: products.php?upload=fail');
exit;
