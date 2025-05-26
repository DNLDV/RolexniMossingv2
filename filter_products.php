<?php
// This file returns only the filtered products section content as JSON
session_start();
$xml = simplexml_load_file("data.xml") or die("Error: Cannot load XML file");

// Get parameters
$selectedCategory = isset($_GET['category']) ? strtolower(trim($_GET['category'])) : '';
$viewMode = isset($_GET['view']) ? $_GET['view'] : 'gallery'; // 'gallery' or 'list'
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 6;

// Filter products by category
$products = $xml->products->product;
$filteredProducts = [];

foreach ($products as $product) {
  $productTags = isset($product->tag) ? strtolower(trim((string)$product->tag)) : '';
  
  if ($selectedCategory === '' || strpos($productTags, $selectedCategory) !== false) {
    $filteredProducts[] = $product;
  }
}

$totalProducts = count($filteredProducts);
$totalPages = ceil($totalProducts / $perPage);
$startIndex = ($currentPage - 1) * $perPage;

$response = [
    'html' => '',
    'totalPages' => $totalPages,
    'currentPage' => $currentPage,
    'totalProducts' => $totalProducts,
    'selectedCategory' => $selectedCategory
];

// Build the HTML for filter info (if category selected)
$filterInfoHtml = '';
if ($selectedCategory) {
    $filterInfoHtml = '<div class="filter-results" id="filter-results-container">
        <p>Showing ' . $totalProducts . ' products for category: <strong>' . ucfirst($selectedCategory) . '</strong></p>
    </div>';
} else {
    // When no category is selected, return empty content
    $filterInfoHtml = '';
}
$response['filterInfo'] = $filterInfoHtml;

// Build the HTML for products
ob_start();
if ($totalProducts > 0):
    for ($i = $startIndex; $i < $startIndex + $perPage && $i < $totalProducts; $i++):
        $product = $filteredProducts[$i];
        $productTags = isset($product->tag) ? explode(',', (string)$product->tag) : [];
?>
    <article class="products__card <?= $viewMode === 'list' ? 'list-item' : '' ?>">
        <img src="<?= $product->image ?>" class="products__img" alt="">
        
        <?php if ($viewMode === 'list'): ?>
            <div class="card-content">
                <div class="product-info">
                    <h3 class="products__title"><?= $product->title ?></h3>
                    <?php if (!empty($productTags)): ?>
                        <div class="product-tags">
                            <?php foreach ($productTags as $tag): ?>
                                <span class="product-tag"><?= trim($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <span class="products__price">₱<?= $product->price ?></span>
                </div>
                <button class="button products__button add-to-cart"
                        data-title="<?= $product->title ?>" data-price="<?= $product->price ?>" data-image="<?= $product->image ?>">
                    Add to Cart
                </button>
            </div>
        <?php else: ?>
            <h3 class="products__title"><?= $product->title ?></h3>
            <?php if (!empty($productTags)): ?>
                <div class="product-tags">
                    <?php foreach (array_slice($productTags, 0, 2) as $tag): ?>
                        <span class="product-tag"><?= trim($tag) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <span class="products__price">₱<?= $product->price ?></span>
            <button class="button products__button add-to-cart"
                    data-title="<?= $product->title ?>" data-price="<?= $product->price ?>" data-image="<?= $product->image ?>">
                Add to Cart
            </button>
        <?php endif; ?>
    </article>
<?php 
    endfor;
else:
?>
    <div class="no-results">
        <i class='bx bx-search'></i>
        <p>No products found. Try another category.</p>
    </div>
<?php
endif;
$response['html'] = ob_get_clean();

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
