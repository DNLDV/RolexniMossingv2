<?php
// This file returns only the products section content as JSON
session_start();
$xml = simplexml_load_file("data.xml") or die("Error: Cannot load XML file");

$products = $xml->products->product;
$totalProducts = count($products);
$perPage = 6;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$totalPages = ceil($totalProducts / $perPage);
$startIndex = ($currentPage - 1) * $perPage;

$response = [
    'html' => '',
    'totalPages' => $totalPages,
    'currentPage' => $currentPage
];

// Build the HTML for products
ob_start();
for ($i = $startIndex; $i < $startIndex + $perPage && $i < $totalProducts; $i++):
    $product = $products[$i];
?>
    <article class="products__card">
        <img src="<?= $product->image ?>" class="products__img" alt="">
        <h3 class="products__title"><?= $product->title ?></h3>
        <span class="products__price">₱<?= $product->price ?></span>
        <button class="button products__button add-to-cart"
                data-title="<?= $product->title ?>" data-price="<?= $product->price ?>" data-image="<?= $product->image ?>">
            Add to Cart
        </button>
    </article>
<?php endfor;
$response['html'] = ob_get_clean();

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
    