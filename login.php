<?php
// Check if a session is not already started before calling session_start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'c:\\inetpub\\wwwroot\\paystubs_resources\\config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'] ?? '';
    
    if ($password === $stored_password) {
        $_SESSION['authenticated'] = true;
        header('Location: default.php');
        exit;
    } else {
        $error = 'Invalid password. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h2>Please enter the password</h2>
    <?php if (isset($error)) : ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <form action="login.php" method="POST">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Submit</button>
    </form>
</body>
</html>
