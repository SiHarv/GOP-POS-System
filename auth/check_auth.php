<?php
if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
   header("Cache-Control: no-cache, no-store, must-revalidate");
   header("Pragma: no-cache");
   header("Expires: 0");
   header("Location: ../../index.php");
   exit;
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
