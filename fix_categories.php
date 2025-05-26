<?php
// Script to fix categories.xml structure
$categoriesFile = "categories.xml";

if (file_exists($categoriesFile)) {
    $content = file_get_contents($categoriesFile);
    
    // Replace <n> tags with <name> tags
    $content = str_replace("<n>", "<name>", $content);
    $content = str_replace("</n>", "</name>", $content);
    
    // Write the updated content back to file
    file_put_contents($categoriesFile, $content);
    
    echo "Categories XML file has been updated successfully!";
} else {
    echo "Categories XML file not found!";
}
?>
