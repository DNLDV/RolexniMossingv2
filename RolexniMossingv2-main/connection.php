<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "rolex_ng_mahirap";

    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>