<?php
session_start();
// Security: redirect if not logged in as admin
if (!isset($_SESSION['admin_user'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

// Load products XML
$productsFile = __DIR__ . '/../data.xml';
if (!file_exists($productsFile)) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Products data file not found.');
}

$xml = simplexml_load_file($productsFile);
if (!$xml) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Failed to load products data.');
}

// Build a unique list of categories
$categories = [];
foreach ($xml->xpath('//product') as $prod) {
    if (isset($prod->category->name)) {
        $n = (string)$prod->category->name;
        $d = (string)$prod->category->description;
        $categories[$n] = $d;
    }
}

// Return categories as JSON
header('Content-Type: application/json');
echo json_encode($categories);
