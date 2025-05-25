<?php
session_start();
// Security: only admin can perform
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.php');
    exit;
}

include __DIR__ . '/../connection.php'; // Database connection

// Handle deletion request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['admin_password'])) {
    $user_id = (int) $_POST['user_id'];
    $providedPwd = $_POST['admin_password'];
    $adminUser = $_SESSION['admin_user'];

    // Fetch hashed password for current admin
    $stmt = $conn->prepare("SELECT password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $adminUser);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if (password_verify($providedPwd, $row['password'])) {
            // Password correct, delete user
            $delStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $delStmt->bind_param("i", $user_id);
            $delStmt->execute();
            header('Location: users.php?delete=success');
            exit;
        }
    }
}
// On failure or invalid access
header('Location: users.php?delete=fail');
exit;
