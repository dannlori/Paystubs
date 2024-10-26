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

$error = ''; // Initialize error variable

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'] ?? '';
    
    if ($password === $stored_password) {
        $_SESSION['authenticated'] = true;
        $_SESSION['last_activity'] = time();
        header('Location: ../default.php');
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
            body, html {
                margin: 0;
                padding: 0;
                height: 100%;
                overflow: hidden; /* Prevent scrolling */
            }

            .background {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-size: cover; /* Cover the entire area */
                background-position: center; /* Center the image */
                z-index: -1; /* Make sure it stays in the background */
            }

            .content {
                position: relative;
                z-index: 1; /* Ensure content is above the background */
                color: white; /* Change text color for contrast */
                text-align: top;
                padding: 20px;
            }

            #main-title {
                font-family: 'Playfair Display', serif; /* Use the fancy font */
                font-size: 8rem; /* Larger font size */
                color: white;
                opacity: 0; /* Start hidden */
                transition: opacity 2s ease; /* Fade transition */
            }

            #auth_error {
                color: yellow;
                border-radius:5px;
                width:fit-content;
                padding:10px;
                margin: 0 auto;
                text-align: center;
                font-weight: bolder;
                font-size: larger;
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
        <div class="background"></div>
        <header class="bg-light py-1">
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
            <?php endif; ?>

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
            <?php if (isset($error)) : ?>
                <p id="auth_error"><?php echo $error; ?></p>
            <?php endif; ?>
        </div>

        <script>
            function showPasswordBox() {
                // Hide the ENTER button
                const enterButton = document.getElementById('enter-button');
                enterButton.style.display = 'none'; // Hide the button

                // Fade in the title
                //const title = document.getElementById('main-title');
                //title.style.opacity = 1; // Set opacity to 1

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
                //const title = document.getElementById('main-title');
                //title.style.opacity = 1; // Fade in title on load
                const enterbuttonspan = document.getElementById('enterbuttonspan');
                enterbuttonspan.style.opacity = 1; // Fade in title on load

                const sessionAlert = document.getElementById('session-alert');
                if (sessionAlert) {
                    setTimeout(() => {
                        sessionAlert.style.display = 'none'; // Hide the alert after 7 seconds
                    }, 7000); // 7000 milliseconds = 7 seconds
                }
            };
            const backgrounds = [
                'url("../images/paystubs1.png")',
                'url("../images/paystubs2.jpg")',
                'url("../images/paystubs3.jpg")',
                'url("../images/paystubs4.jpg")'
            ];

            // Select a random background image
            const randomIndex = Math.floor(Math.random() * backgrounds.length);
            const backgroundDiv = document.querySelector('.background');
            backgroundDiv.style.backgroundImage = backgrounds[randomIndex];
        </script>
    </body>
</html>
