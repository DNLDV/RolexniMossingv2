<?php
session_start();
header('Content-Type: application/json');

// Load XML file
$xml = simplexml_load_file("data.xml") or die("Error: Cannot load XML file");

// Get search parameters
$query = isset($_GET['query']) ? strtolower(trim($_GET['query'])) : '';
$products = $xml->products->product;
$results = array();

if (!empty($query)) {
    foreach ($products as $product) {
        // Search in title
        $title = strtolower($product->title);
        // Search in tag
        $tag = strtolower($product->tag);
        // Search in category (if it exists)
        $category = isset($product->category->name) ? strtolower($product->category->name) : '';
        
        // If query matches any of the fields, add to results
        if (strpos($title, $query) !== false || 
            strpos($tag, $query) !== false || 
            strpos($category, $query) !== false) {
            
            $results[] = array(
                'title' => (string)$product->title,
                'price' => (string)$product->price,
                'tag' => (string)$product->tag,
                'image' => (string)$product->image,
                'category' => $category
            );
        }
    }
}

// Return search results
echo json_encode(array(
    'success' => true,
    'results' => $results,
    'count' => count($results)
));
?>
