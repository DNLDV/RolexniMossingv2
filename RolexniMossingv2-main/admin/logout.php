<?php
// filepath: c:\xampp\htdocs\RolexniMossingv2\admin\logout.php
session_start();
// Unset and destroy session
session_unset();
session_destroy();
// Redirect to admin login
header('Location: index.php');
exit;
