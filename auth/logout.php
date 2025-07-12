<?php
session_start();

$_SESSION = [];

if (ini_get("session.use_cookies")) {
   $params = session_get_cookie_params();
   setcookie(
      session_name(),
      '',
      time() - 42000,
      $params["path"],
      $params["domain"],
      $params["secure"],
      $params["httponly"]
   );
}

session_destroy();

setcookie('authenticated', '', time() - 3600, '/');

// Prevent caching to avoid back button access
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

header("Location: ../index.php");
exit;
