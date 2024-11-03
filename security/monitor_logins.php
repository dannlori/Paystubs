<?php
date_default_timezone_set('America/Denver'); // Adjust this to your desired time zone
$credentialsFile = 'C:\\inetpub\\wwwroot\\paystubs_resources\\db.info';  // Change this to the actual path
// Read and parse the credentials file
if (file_exists($credentialsFile)) {
    $dbCredentials = parse_ini_file($credentialsFile);

    // Assign credentials to variables
    $dbUsername = $dbCredentials['username'];
    $dbPassword = $dbCredentials['password'];
} else {
    die('Error: Credentials file not found.');
}
// PDO database connection
$dsn = 'mysql:host=localhost;dbname=earnings';
$username = $dbUsername;
$password_db = $dbPassword;
$options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
);

$pdo = new PDO($dsn, $username, $password_db, $options);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function recordLoginAttempt($ip)
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, attempts) VALUES (?, 1) ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = NOW()");
    $stmt->execute([$ip]);
}

function getLoginAttempts($ip)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT attempts, last_attempt FROM login_attempts WHERE ip_address = ?");
    $stmt->execute([$ip]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function resetLoginAttempts($ip)
{
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
    $stmt->execute([$ip]);
}

$ipAddress = $_SERVER['REMOTE_ADDR'];
$loginAttempts = getLoginAttempts($ipAddress);
$maxAttempts = 5;
$lockoutDuration = 300; // Lockout duration in seconds (e.g., 5 minutes)
$currentTime = time();
$error = ""; // Initalizing the error variable
$showLogin = true; // Setting initial state to true
$ipReleased = false;

if ($loginAttempts) {
    $attempts = $loginAttempts['attempts'];
    $lastAttemptTime = strtotime($loginAttempts['last_attempt']);


    if ($attempts >= $maxAttempts) {
        // Check if the lockout duration has passed
        if ($currentTime - $lastAttemptTime < $lockoutDuration) {
            $showLogin = false;
            $error .= "Too many failed attempts.<br/>";
            //exit;
        } else {
            // Reset attempts if lockout duration has passed
            $showLogin = true;
            $ipReleased = true;
            resetLoginAttempts($ipAddress);
            $error .= "IP released. Please try again.<br/>";
        }
    }
}
