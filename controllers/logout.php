<?php
session_start(); // Start session to access it

// Clear session from server-side
$_SESSION = array(); // Clear all session variables
session_destroy(); // Destroy the session

// Clear the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Clear any other cookies if needed
setcookie('PHPSESSID', '', time() - 3600, '/');

// Ensure no cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect to login page
header("Location: ../Classes/signin.php");
exit;