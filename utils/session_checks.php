<?php
session_start();

// Function to check if the user is logged in based on a session key and redirect if not
function check_session($session_key, $redirect_url) {
    if (!isset($_SESSION[$session_key])) {
        // Redirect to the specified URL if the session key is not set
        header("Location: " . $redirect_url);
        exit();
    }
}

?>