// Check Session and if over 5 min of inactivity, Logout
function checkSession() {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "security/session_check.php", true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.status === 'timeout') {
                console.log('Session Data:', response.sessionData);
                //alert("Your session has expired. You will be redirected to the login page.");
                window.location.href = 'security/login.php?s=timeout'; // Redirect to login page
            } else if (response.status === 'not_logged_in') {
                window.location.href = 'security/login.php?s=nologin'; // Redirect if not logged in
            }
        }
    };
    xhr.send();
}

// Check the session every 322 seconds
setInterval(checkSession, 322000);

// Show the custom alert after 290 seconds (290,000 milliseconds)
setTimeout(function() {
    const alertBox = document.getElementById("sessionAlert");
    alertBox.style.display = "block"; // Show the alert

    let timeLeft = 29; // Countdown time in seconds
    const countdownElement = document.getElementById("countdown");

    // Update the countdown every second
    const countdownInterval = setInterval(function() {
        countdownElement.textContent = timeLeft; // Update the displayed time
        timeLeft--;

        if (timeLeft < 0) {
            clearInterval(countdownInterval); // Stop the countdown
            alertBox.style.opacity = 0; // Start fading out
            setTimeout(function() {
                alertBox.style.display = "none"; // Hide the alert after fading out
            }, 1000); // Wait for the fade effect to complete
        }
    }, 1000); // Update every second

    // Close button functionality
    const closeButton = document.getElementById("closeAlert");
    closeButton.onclick = function() {
        clearInterval(countdownInterval); // Clear the countdown interval
        alertBox.style.opacity = 0; // Start fading out
        setTimeout(function() {
            alertBox.style.display = "none"; // Hide the alert after fading out
        }, 1000); // Wait for the fade effect to complete
    };

}, 290000); // 290 seconds in milliseconds