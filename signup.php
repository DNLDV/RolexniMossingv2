<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $fullname = $_POST['fullname'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  // TODO: Save the user in a database (replace below with DB code)
  $_SESSION['user'] = ['name' => $fullname, 'email' => $email];

  // Redirect back to homepage
  header("Location: index.php");
  exit();
}
?>
