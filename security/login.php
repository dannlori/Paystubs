<?php

// Check if a session is not already started before calling session_start
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 1800);
    session_start();
    $_SESSION['authenticated'] = false;

}
require_once 'c:\\inetpub\\wwwroot\\paystubs_resources\\config.php';

// For monitoring logins and blocking
require_once '../security/monitor_logins.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'] ?? '';
    
    if ($password === $stored_password) {
        $_SESSION['authenticated'] = true;
        $_SESSION['last_activity'] = time();
        resetLoginAttempts($ipAddress); // Reset on successful login
        header('Location: ../default.php');
        exit;
    } else {
        recordLoginAttempt($ipAddress);
        if ($ipReleased == false) {
            $error .= '<span id="invalid_password">Invalid password. Please try again.</span>';
    
        }
    }
}

// Check for the 's' parameter in the URL
if (isset($_GET['s'])) {
    switch ($_GET['s']) {
        case 'timeout':
            $session_error = '<span id="session-alert" class="alert alert-warning">Your session has expired. Please log in again.</span>';
            break;
        case 'logout':
            $session_error = '<span id="session-alert" class="alert alert-info">You have successfully logged out.</span>';
            break;
        case 'nologin':
            $session_error = '<span id="session-alert" class="alert alert-info">No session found. Please login!</span>';
            break;
        
        default:
            break;
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
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet"> <!-- Fancy font -->
        <link rel="stylesheet" href="../css/common.css">
        <link rel="stylesheet" href="../css/login.css">

    </head>
    <body>
        <div class="background"></div>
        <header class="py-1 header">
            <div class="container text-center">
                <h4>
                    <i class="fas fa-lock"></i> Authentication required to access site
                </h4>
            </div>
        </header>

        <div class="container text-center content">

            <!-- Display the alert message if it exists -->
            <?php if (!empty($session_error)) : ?>
                <div class="my-3">
                    <?php echo "<br/>" . $session_error; ?>
                </div>
            <?php endif;
            if ($showLogin && $showLogin == true) {
            ?>

                <span id="enterbuttonspan"><button id="enter-button" class="btn btn-primary" onclick="showPasswordBox()">ENTER</button></span>

                <div id="password-container">
                    <form action="login.php" method="POST">
                        <label for="password" title="Enter Password!"><i class="fas fa-key fa-2x"></i></label>
                        <input type="password" id="password" name="password" title="Enter Password!" required autofocus placeholder="Enter Password">
                        <button type="submit" id="submit-button"><i class="fas fa-arrow-circle-right"></i></button>
                    </form>
                    <br/>
                </div>
                <br/><br/>
            <?php 
            } else {
                echo '<span id="enterbuttonspan"></span>';
            }

            if (isset($error) && $error != "") : ?>
                <p id="auth_error"><?php echo $error; ?></p>
            <?php endif; ?>
        </div>

        <script src="../js/background.js"></script>
        <script>
            function showPasswordBox() {
                // Hide the ENTER button
                const enterButton = document.getElementById('enter-button');
                enterButton.style.display = 'none'; // Hide the button


                const enterbuttonspan = document.getElementById('enterbuttonspan');
                enterbuttonspan.style.opacity = 1; // Set opacity to 1

                // Show and fade in the password container
                const passwordContainer = document.getElementById('password-container');
                passwordContainer.style.display = 'block'; // Make it visible

                setTimeout(() => {
                    passwordContainer.style.opacity = 1; // Set opacity to 1 after the display change
                    document.getElementById('password').focus(); // Set focus on the password field
                }, 150); // A slight delay to ensure display is set
            }

            // Show the title with a delay
            window.onload = () => {
                const enterbuttonspan = document.getElementById('enterbuttonspan');
                enterbuttonspan.style.opacity = 1; // Fade in title on load

                const sessionAlert = document.getElementById('session-alert');
                if (sessionAlert) {
                    setTimeout(() => {
                        sessionAlert.style.display = 'none'; // Hide the alert after 7 seconds
                    }, 7000); // 7000 milliseconds = 7 seconds
                }

                const authError = document.getElementById('auth_error');
                if (authError) {
                    setTimeout(() => {
                        authError.style.display = 'none'; // Hide the alert after 7 seconds
                    }, 5000); // 5000 milliseconds = 5 seconds
                }
            };
            
        </script>
        </body>
</html>
