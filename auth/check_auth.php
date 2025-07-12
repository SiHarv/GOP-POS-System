<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

// Check if user is authenticated
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
   // User is not authenticated, redirect to login
   header("Cache-Control: no-cache, no-store, must-revalidate");
   header("Pragma: no-cache");
   header("Expires: 0");
   header("Location: ../../index.php");
   exit;
}

// Prevent browser caching of authenticated pages
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
