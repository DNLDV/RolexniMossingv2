<?php
require 'assets/phpmailer/PHPMailer-master/src/PHPMailer.php';
require 'assets/phpmailer/PHPMailer-master/src/SMTP.php';
require 'assets/phpmailer/PHPMailer-master/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'your_real_gmail@gmail.com'; // <-- Replace with your Gmail
    $mail->Password = 'abcdefghijklnop'; // <-- Replace with your 16-char App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('your_real_gmail@gmail.com', 'Test');
    $mail->addAddress('your_real_gmail@gmail.com'); // <-- You can use your own Gmail for testing
    $mail->Subject = 'Test Email from PHPMailer';
    $mail->Body = 'This is a test email sent using PHPMailer.';

    $mail->send();
    echo 'Message sent! Check your inbox.';
} catch (Exception $e) {
    echo 'Mailer Error: ' . $mail->ErrorInfo;
}
