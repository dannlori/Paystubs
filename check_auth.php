<?php
// Check if a session is not already started before calling session_start
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true || session_status() == PHP_SESSION_NONE) {
    header('Location: login.php');
    exit;
}

// Set the session timeout duration to 30 minutes (in seconds)
$timeout_duration = 1800; // 30 minutes

// Check if the "last activity" timestamp is set in the session
if (isset($_SESSION['last_activity'])) {
    // Calculate the time elapsed since the last activity
    $elapsed_time = time() - $_SESSION['last_activity'];

    // If the elapsed time is greater than the timeout duration, destroy the session
    if ($elapsed_time > $timeout_duration) {
        session_unset();     // Unset session variables
        session_destroy();   // Destroy the session
        header("Location: login.php"); // Redirect to login page or other action
        exit;
    }
}

// Update the last activity time
$_SESSION['last_activity'] = time();
