<?php
session_start();

include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $fullname = $_POST['fullname'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);


  $check_email = $conn->prepare("SELECT email FROM users WHERE email = ?");
  $check_email->bind_param("s", $email);
  $check_email->execute();
  $result = $check_email->get_result();

  if ($result->num_rows > 0) {
      $_SESSION['error'] = "Email already registered";
      header("Location: index.php");
      exit();
  }

  $stmt = $conn->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $fullname, $email, $password);

  if ($stmt->execute()) {
      $_SESSION['user'] = ['name' => $fullname, 'email' => $email];
      header("Location: index.php");
  } else {
      $_SESSION['error'] = "Registration failed: " . $conn->error;
      header("Location: index.php");
  }
  $stmt->close();
}
?>
