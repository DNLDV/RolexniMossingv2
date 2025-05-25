<?php
session_start();

if ($_POST['email'] === 'test@example.com' && $_POST['password'] === 'password') {
  $_SESSION['user'] = $_POST['email'];
  header("Location: index.php");
} else {
  echo "<script>alert('Invalid credentials'); window.location.href='index.php';</script>";
}
?>
