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
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet"> <!-- Fancy font -->

        <style>
            html, body {
                margin: 0;
                padding: 0;
                height: 100%;
                background: linear-gradient(to right, #ff7e5f, #feb47b); /* Adjust colors */
            }
            #main-title {
                font-family: 'Playfair Display', serif; /* Use the fancy font */
                font-size: 8rem; /* Larger font size */
                color: white;
                opacity: 0; /* Start hidden */
                transition: opacity 2s ease; /* Fade transition */
            }
            #password-container {
                display: none; /* Initially hidden */
                opacity: 0; /* Start hidden */
                transition: opacity 2s ease; /* Fade transition */
            }
            #password {
                border-radius: 10px;
                padding: 15px;
                border: 1px solid #ccc;
                width: 300px;
                font-size: 24px;
            }
            #submit-button {
                border-radius: 8px;
                padding: 12px 24px;
                border: none;
                background-color: #4CAF50;
                color: white;
                font-size: 24px;
                cursor: pointer;
            }
            #submit-button:hover {
                background-color: #45a049;
            }
            #enter-button {
                font-family: 'Playfair Display', serif; /* Apply the fancy font */
                font-size: 24px; /* Larger font size */
                padding: 12px 30px; /* Increased padding */
                border: none; /* Remove border */
                background-color: #4CAF50; /* Button color */
                color: white; /* Button text color */
                transition: background-color 0.3s; /* Transition for hover effect */
            }
            #enter-button:hover {
                background-color: #45a049; /* Darker shade on hover */
            }
            #enterbuttonspan {
                opacity: 0; /* Start hidden */
                transition: opacity 2s ease; /* Fade transition */
            }
        </style>
    </head>
    <body>
        <header class="bg-light py-1">
            <div class="container text-center">
                <h4>
                    <i class="fas fa-lock"></i> Authentication required to access site
                </h4>
            </div>
        </header>

        <div class="container text-center" style="margin-top: 100px;">
            <h1 id="main-title">PAYSTUB$</h1>
            <span id="enterbuttonspan"><button id="enter-button" class="btn btn-primary" onclick="showPasswordBox()">ENTER</button></span>

            <div id="password-container">
                <form action="login.php" method="POST">
                    <label for="password" title="Enter Password!"><i class="fas fa-key fa-2x"></i></label>
                    <input type="password" id="password" name="password" title="Enter Password!" required autofocus placeholder="Enter Password">
                    <button type="submit" id="submit-button"><i class="fas fa-arrow-circle-right"></i></button>
                </form>
                <br/><br/>
                <?php if (isset($error)) : ?>
                    <p style="color: red;"><?php echo $error; ?></p>
                <?php endif; ?>
            </div>
        </div>

        <script>
            function showPasswordBox() {
                // Hide the ENTER button
                const enterButton = document.getElementById('enter-button');
                enterButton.style.display = 'none'; // Hide the button

                // Fade in the title
                const title = document.getElementById('main-title');
                title.style.opacity = 1; // Set opacity to 1

                const enterbuttonspan = document.getElementById('enterbuttonspan');
                enterbuttonspan.style.opacity = 1; // Set opacity to 1

                // Show and fade in the password container
                const passwordContainer = document.getElementById('password-container');
                passwordContainer.style.display = 'block'; // Make it visible
                setTimeout(() => {
                    passwordContainer.style.opacity = 1; // Set opacity to 1 after the display change
                }, 150); // A slight delay to ensure display is set
            }

            // Show the title with a delay
            window.onload = () => {
                const title = document.getElementById('main-title');
                title.style.opacity = 1; // Fade in title on load
                const enterbuttonspan = document.getElementById('enterbuttonspan');
                enterbuttonspan.style.opacity = 1; // Fade in title on load
            };
        </script>
    </body>
</html>
