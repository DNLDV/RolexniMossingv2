<?php
session_start();

include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, fullname, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['fullname'],
                'email' => $user['email']
            ];
            
            header("Location: index.php");
            exit();
        } else {
            // Password is incorrect
            $_SESSION['error'] = "Invalid email or password";
        }
    } else {
        // User not found
        $_SESSION['error'] = "Invalid email or password";
    }
    
    // Authentication failed
    header("Location: index.php");
    exit();
}
?>
