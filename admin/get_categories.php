<?php
session_start();
// Security: redirect if not logged in as admin
if (!isset($_SESSION['admin_user'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

// Load categories XML
$categoriesFile = __DIR__ . '/../categories.xml';
if (!file_exists($categoriesFile)) {
    // Create empty categories file if it doesn't exist
    $xml = new SimpleXMLElement('<categories></categories>');
    $xml->asXML($categoriesFile);
}

$xml = simplexml_load_file($categoriesFile);
if (!$xml) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Failed to load categories data.');
}

// Build a list of categories
$categories = [];
foreach ($xml->category as $category) {
    $categories[(string)$category->name] = (string)$category->description;
}

// Return categories as JSON
header('Content-Type: application/json');
echo json_encode($categories);
