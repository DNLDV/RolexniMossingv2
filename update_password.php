<?php
session_start();
include 'connection.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (!$token || !$password || !$confirm) {
        $_SESSION['error'] = 'All fields are required.';
        header('Location: index.php');
        exit();
    }
    if ($password !== $confirm) {
        $_SESSION['error'] = 'Passwords do not match.';
        header('Location: reset_password.php?token=' . urlencode($token));
        exit();
    }
    // Check token and expiry
    $stmt = $conn->prepare('SELECT id, reset_expires FROM users WHERE reset_token = ?');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'Invalid or expired reset link.';
        header('Location: index.php');
        exit();
    }
    $user = $result->fetch_assoc();
    if (strtotime($user['reset_expires']) < time()) {
        $_SESSION['error'] = 'Reset link has expired.';
        header('Location: index.php');
        exit();
    }
    // Update password
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?');
    $stmt->bind_param('si', $hashed, $user['id']);
    $stmt->execute();
    $_SESSION['success'] = 'Your password has been updated. You can now log in.';
    header('Location: index.php');
    exit();
}
header('Location: index.php');
exit();
