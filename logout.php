<?php
// Check if a session is not already started before calling session_start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
session_destroy();
header('Location: login.php');
exit;