<?php
// Check if a session is not already started before calling session_start
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 1800);
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
        <style>
            body {
               background: linear-gradient(to right, #ff7e5f, #feb47b); /* Adjust colors */
            }
            #password {
                border-radius: 10px;  /* Adjust the value for roundness */
                padding: 5px;
                border: 1px solid #ccc;
                width: 150px;  /* Adjust width as needed */
                font-size: 12px;
            }
            #submit-button {
                border-radius: 8px;  /* Adjust the value for roundness */
                padding: 6px 12px;
                border: none;
                background-color: #4CAF50;  /* Button color */
                color: white;
                font-size: 12px;
                cursor: pointer;
            }

            #submit-button:hover {
                background-color: #45a049;  /* Darker shade on hover */
            }
        </style>
    </head>
    <body>
        <h2>
            <center>Password required to access site</center>
        </h2>
        <p>
            <center>
                <?php if (isset($error)) : ?>
                    <p style="color: red;"><?php echo $error; ?></p>
                <?php endif; ?>
                <form action="login.php" method="POST">
                    <label for="password"><b>Password:</b></label>
                    <input type="password" id="password" name="password" required autofocus placeholder="Enter Password">
                    <button type="submit" id="submit-button">Submit</button>
                </form>
            </center>
        </p>
    </body>
</html>
