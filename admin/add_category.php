<?php
session_start();
// Security: only admin can perform
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_index'], $_POST['cat_name'], $_POST['cat_desc'])) {
    $index = (int) $_POST['product_index'];
    $catName = trim($_POST['cat_name']);
    $catDesc = trim($_POST['cat_desc']);
    $type = $_POST['type'] ?? 'products'; // 'featured' or 'products'

    $file = __DIR__ . '/../data.xml';
    $doc = new DOMDocument();
    $doc->preserveWhiteSpace = false;
    $doc->formatOutput = true;

    if ($doc->load($file)) {
        $xpath = new DOMXPath($doc);
        // Determine node list path
        $path = $type === 'featured' ? '/shop/featuredProducts/product' : '/shop/products/product';
        $productNodes = $xpath->query($path);

        if ($productNodes->length > $index) {
            $prodNode = $productNodes->item($index);
            // Remove existing category
            foreach ($prodNode->getElementsByTagName('category') as $existing) {
                $prodNode->removeChild($existing);
            }
            // Create new category element
            $catElem = $doc->createElement('category');
            $nameElem = $doc->createElement('name', htmlspecialchars($catName));
            $descElem = $doc->createElement('description', htmlspecialchars($catDesc));
            $catElem->appendChild($nameElem);
            $catElem->appendChild($descElem);
            $prodNode->appendChild($catElem);
            // Save changes
            if ($doc->save($file)) {
                header('Location: products.php?added=success');
                exit;
            }
        }
    }
}
// On failure redirect
header('Location: products.php?added=fail');
exit;
