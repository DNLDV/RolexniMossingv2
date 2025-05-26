<?php
session_start();
// Security: only admin can perform actions
if (!isset($_SESSION['admin_user'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'fail', 'message' => 'Unauthorized access']);
    exit;
}

$categoriesFile = __DIR__ . '/../categories.xml';

// Create categories file if it doesn't exist
if (!file_exists($categoriesFile)) {
    $xml = new SimpleXMLElement('<categories></categories>');
    $xml->asXML($categoriesFile);
}

// Load existing categories
$xml = simplexml_load_file($categoriesFile);
if (!$xml) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'fail', 'message' => 'Failed to load categories']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = trim($_POST['category_name'] ?? '');
    $categoryDescription = trim($_POST['category_description'] ?? '');
    
    if (empty($categoryName) || empty($categoryDescription)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'fail', 'message' => 'Category name and description are required']);
        exit;
    }
    
    // Check if category already exists
    $categoryExists = false;
    foreach ($xml->category as $category) {
        if (strtolower((string)$category->name) === strtolower($categoryName)) {
            $categoryExists = true;
            break;
        }
    }
    
    if ($categoryExists) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'fail', 'message' => 'Category already exists']);
        exit;
    }
    
    // Add new category
    $newCategory = $xml->addChild('category');
    $newCategory->addChild('name', $categoryName);
    $newCategory->addChild('description', $categoryDescription);
    
    // Save XML
    if ($xml->asXML($categoriesFile)) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success', 
            'message' => 'Category created successfully',
            'category' => [
                'name' => $categoryName,
                'description' => $categoryDescription
            ]
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'fail', 'message' => 'Failed to save category']);
    }
    exit;
}

// Invalid request method
header('Content-Type: application/json');
echo json_encode(['status' => 'fail', 'message' => 'Invalid request method']);
exit;
