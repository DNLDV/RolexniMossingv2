<?php
session_start();
// Security: only admin can perform
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_index'])) {
    $index = (int) $_POST['product_index'];
    $type = $_POST['type'] ?? 'products'; // 'featured' or 'products'
    $file = __DIR__ . '/../data.xml';
    $doc = new DOMDocument();
    $doc->preserveWhiteSpace = false;
    $doc->formatOutput = true;

    if ($doc->load($file)) {
        $xpath = new DOMXPath($doc);
        // Determine node list path
        $path = $type === 'featured' ? '/shop/featuredProducts/product' : '/shop/products/product';
        $nodes = $xpath->query($path);

        if ($nodes->length > $index) {
            $prodNode = $nodes->item($index);
            // Remove <category> child nodes
            foreach ($prodNode->getElementsByTagName('category') as $cat) {
                $prodNode->removeChild($cat);
            }
            $doc->save($file);
        }
    }
}
header('Location: products.php?removed=success');
exit;
