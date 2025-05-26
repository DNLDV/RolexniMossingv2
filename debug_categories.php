<?php
// Debug file to check categories XML structure
$categoriesFile = "categories.xml";

echo "<h1>Categories XML Debug</h1>";

if (file_exists($categoriesFile)) {
    echo "<p>File exists: Yes</p>";
    
    $xml = simplexml_load_file($categoriesFile);
    if ($xml === false) {
        echo "<p>Error parsing XML file</p>";
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            echo "<p>Error: " . $error->message . "</p>";
        }
    } else {
        echo "<p>XML file loaded successfully</p>";
        echo "<p>Root name: " . $xml->getName() . "</p>";
        
        echo "<h2>Structure:</h2>";
        echo "<pre>";
        print_r($xml);
        echo "</pre>";
        
        echo "<h2>Categories:</h2>";
        echo "<ul>";
        foreach ($xml->category as $cat) {
            echo "<li>";
            echo "Node name: " . $cat->getName();
            
            // Check for the name tag
            if (isset($cat->name)) {
                echo " | Name tag: " . $cat->name;
            } else {
                echo " | Name tag: NOT FOUND";
            }
            
            // Check for the n tag
            if (isset($cat->n)) {
                echo " | N tag: " . $cat->n;
            } else {
                echo " | N tag: NOT FOUND";
            }
            
            // Check for description
            if (isset($cat->description)) {
                echo " | Description: " . $cat->description;
            }
            
            echo "</li>";
        }
        echo "</ul>";
        
        echo "<h2>Direct XML Source:</h2>";
        echo "<textarea style='width:100%; height:300px;'>";
        echo htmlspecialchars(file_get_contents($categoriesFile));
        echo "</textarea>";
    }
} else {
    echo "<p>File exists: No</p>";
}
?>
