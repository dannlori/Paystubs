<?php
// Set the session timeout duration to 5 minutes (in seconds)
$timeout_duration = 310; // 5 minutes

// Check if the "last activity" timestamp is set in the session
if (isset($_SESSION['last_activity'])) {
    // Calculate the time elapsed since the last activity
    $elapsed_time = time() - $_SESSION['last_activity'];

    // If the elapsed time is greater than the timeout duration, destroy the session
    if ($elapsed_time > $timeout_duration) {
        echo json_encode([
            'status' => 'timeout',
            'sessionData' => [
                'Authenticated' => $_SESSION['authenticated'] ?? null,
                'Elapsed_time' => $elapsed_time . ' seconds' ?? null,
                'lastActivity' => $_SESSION['last_activity']
            ]
        ]);
        session_unset();     // Unset session variables
        session_destroy();   // Destroy the session
        exit;
    }

    // Update the last activity time
    $_SESSION['last_activity'] = time();
    echo json_encode([
        'status' => 'active',
        'sessionData' => [
            'Authenticated' => $_SESSION['authenticated'] ?? null,
            'Elapsed_time' => $elapsed_time . ' seconds' ?? null,
            'lastActivity' => $_SESSION['last_activity']
        ]
    ]);
} else {
    // If not logged in
    echo json_encode(['status' => 'not_logged_in']);
}
