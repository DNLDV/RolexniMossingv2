<?php
session_start();

include 'connection.php';
require_once __DIR__ . '/assets/phpmailer/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/assets/phpmailer/PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/assets/phpmailer/PHPMailer-master/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVerificationEmail($email, $token, &$mailError = null) {
    $mail = new PHPMailer(true);
    try {
        // Enable SMTP debugging
        $mail->SMTPDebug = 2; // 2 = client and server messages
        $mail->Debugoutput = function($str, $level) {
            file_put_contents(__DIR__ . '/phpmailer_debug.log', $str . "\n", FILE_APPEND);
        };
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'shaasudesu@gmail.com'; // Your Gmail address
        $mail->Password   = 'downnakatmgethma'; // Your 16-character App Password, no spaces
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom('your_new_gmail@gmail.com', 'PFRolex');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'PFRolex Account Verification';
        $verifyLink = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/verify.php?token=" . urlencode($token);
        $mail->Body    = "<h2>Welcome to PFRolex!</h2><p>Click the link below to verify your email and activate your account:</p><a href='$verifyLink'>$verifyLink</a>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        $mailError = $mail->ErrorInfo;
        // Also try to read the debug log and append it to the error
        $debugLog = @file_get_contents(__DIR__ . '/phpmailer_debug.log');
        if ($debugLog) {
            $mailError .= "\nDebug log:\n" . $debugLog;
        }
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // === reCAPTCHA validation with debug ===
    $recaptchaSecret = '6Lctn0krAAAAAESLU3kNNqpArJ8y0DzVgxhfjq5h'; // Your real secret key from Google reCAPTCHA admin panel
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
    $userIP = $_SERVER['REMOTE_ADDR'] ?? '';
    $verifyResponse = file_get_contents(
        "https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecret}&response={$recaptchaResponse}&remoteip={$userIP}"
    );
    $responseData = json_decode($verifyResponse, true);
    if (!$responseData || !$responseData['success']) {
        $_SESSION['error'] = "CAPTCHA verification failed. Debug: " . htmlspecialchars($verifyResponse);
        header("Location: index.php");
        exit();
    }
    // === end reCAPTCHA validation ===

  $fullname = $_POST['fullname'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $token = bin2hex(random_bytes(16));

  $check_email = $conn->prepare("SELECT email FROM users WHERE email = ?");
  $check_email->bind_param("s", $email);
  $check_email->execute();
  $result = $check_email->get_result();

  if ($result->num_rows > 0) {
      $_SESSION['error'] = "Email already registered";
      header("Location: index.php");
      exit();
  }

  $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, is_verified, verification_token) VALUES (?, ?, ?, 0, ?)");
  $stmt->bind_param("ssss", $fullname, $email, $password, $token);

  if ($stmt->execute()) {
      $mailError = null;
      if (sendVerificationEmail($email, $token, $mailError)) {
          $_SESSION['success'] = "Registration successful! Please check your email to verify your account.";
      } else {
          $_SESSION['error'] = "Registration successful, but failed to send verification email: $mailError";
      }
      header("Location: index.php");
  } else {
      $_SESSION['error'] = "Registration failed: " . $conn->error;
      header("Location: index.php");
  }
  $stmt->close();
}
?>
