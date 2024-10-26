<?php
//echo '<pre>'; // Optional: to format the output
//print_r($_SESSION);
//echo '</pre>';
// Check if a session is not already started before calling session_start
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 1800);
    session_start();
    $_SESSION['authenticated'] = false;

}
require_once 'c:\\inetpub\\wwwroot\\paystubs_resources\\config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'] ?? '';
    
    if ($password === $stored_password) {
        $_SESSION['authenticated'] = true;
        $_SESSION['last_activity'] = time();
        header('Location: default.php');
        exit;
    } else {
        $error = '<span id="invalid_password">Invalid password. Please try again.</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">  
        <!-- Font Awesome CSS -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <style>
            html, body {
                margin: 0;
                padding: 0;
                height: 100%;
                background: linear-gradient(to right, #ff7e5f, #feb47b); /* Adjust colors */
            }
            #password {
                border-radius: 10px;  /* Adjust the value for roundness */
                padding: 5px;
                border: 1px solid #ccc;
                width: 200px;  /* Adjust width as needed */
                font-size: 20px;
            }

            #invalid_password {
                padding: 5px;
                background-color: gray; /* Black box behind the text */
                border-radius: 10px;  /* Adjust the value for roundness */
                padding: 10px 20px; /* Padding inside the black box */
                display: inline-block; /* Ensure the box wraps tightly around the text */
                font-size: 20px;
                font-weight: bolder;
                color: red; /* Make the text red to indicate failure */
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5); /* Shadow settings */
                font-weight: bold; /* Optional: makes the text bold */
            }

            #submit-button {
                border-radius: 8px;  /* Adjust the value for roundness */
                padding: 6px 12px;
                border: none;
                background-color: #4CAF50;  /* Button color */
                color: white;
                font-size: 20px;
                cursor: pointer;
            }

            #submit-button:hover {
                background-color: #45a049;  /* Darker shade on hover */
            }
        </style>
    </head>
    <body>
        <header class="bg-light py-1">
            <div class="container">
                <div class="row align-items-center justify-content-between">
                    <h2>
                        <center><i class="fas fa-lock"></i> Authentication required to access site</center>
                    </h2>
                </div>
            </div>
        </header>

        <p>
            <center>
                <form action="login.php" method="POST">
                    <label for="password" title="Enter Password!"><i class="fas fa-key fa-2x"></i></label>
                    <input type="password" id="password" name="password" title="Enter Password!" required autofocus placeholder="Enter Password">
                    <button type="submit" id="submit-button"><i class="fas fa-arrow-circle-right"></i></button>
                </form>
                <br/><br/>
                <?php if (isset($error)) : ?>
                    <p style="color: red;"><?php echo $error; ?></p>
                <?php endif; ?>
            </center>
        </p>
    </body>
</html>
