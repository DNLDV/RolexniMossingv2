<?php
session_start();
include 'connection.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $conn->prepare("SELECT id, is_verified FROM users WHERE verification_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if ($user['is_verified']) {
            $_SESSION['success'] = "Account already verified. You can log in.";
        } else {
            $update = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
            $update->bind_param("i", $user['id']);
            $update->execute();
            $_SESSION['success'] = "Your account has been verified! You can now log in.";
        }
    } else {
        $_SESSION['error'] = "Invalid or expired verification link.";
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "No verification token provided.";
}
header("Location: index.php");
exit;
