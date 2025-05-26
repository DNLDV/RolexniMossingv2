<?php
session_start();
include 'connection.php';
require_once __DIR__ . '/assets/phpmailer/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/assets/phpmailer/PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/assets/phpmailer/PHPMailer-master/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email address.';
        header('Location: forgot_password.php');
        exit();
    }
    // Check if user exists
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'No account found with that email.';
        header('Location: forgot_password.php');
        exit();
    }
    $user = $result->fetch_assoc();
    $userId = $user['id'];
    // Generate reset token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    // Store token and expiry in DB (add columns if needed)
    $stmt = $conn->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?');
    $stmt->bind_param('ssi', $token, $expires, $userId);
    $stmt->execute();
    // Send email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'shaasudesu@gmail.com';
        $mail->Password = 'downnakatmgethma';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom('shaasudesu@gmail.com', 'PFRolex');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'PFRolex Password Reset';
        $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . urlencode($token);
        $mail->Body = "<h2>Password Reset Request</h2><p>Click the link below to reset your password. This link will expire in 1 hour.</p><a href='$resetLink'>$resetLink</a>";
        $mail->send();
        $_SESSION['success'] = 'A password reset link has been sent to your email.';
        header('Location: forgot_password.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = 'Failed to send reset email: ' . $mail->ErrorInfo;
        header('Location: forgot_password.php');
        exit();
    }
}
header('Location: forgot_password.php');
exit();
