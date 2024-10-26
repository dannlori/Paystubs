<?php
// Check if a session is not already started before calling session_start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
session_unset();     // Unset session variables
session_destroy();   // Destroy the session
//echo '<pre>'; // Optional: to format the output
//print_r($_SESSION);
//echo '</pre>';
header('Location: login.php');
exit;
