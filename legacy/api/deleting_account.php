<?php
/**
 * API Endpoint for handling account deletion requests.
 *
 * Responds to POST requests with JSON data.
 *
 * @method POST
 * @param string email The user's email address.
 * @param string|null reason The optional reason for deletion.
 * @return json { "success": bool, "message": string }
 */

// Set the content type header to signal a JSON response
header('Content-Type: application/json');

// --- Database Connection ---
// In a real application, ensure this path is correct and secure.
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');


// --- Function to send JSON response and exit ---
function send_response($success, $message, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// --- Main Logic ---

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(false, 'Invalid request method. Please use POST.', 405);
}

// Get the raw POST data
$json_data = file_get_contents('php://input');
$data = json_decode($json_data);

// Check if JSON decoding was successful and data exists
if (json_last_error() !== JSON_ERROR_NONE || !$data) {
    send_response(false, 'Invalid JSON data provided.', 400);
}

// Sanitize and retrieve form data from the decoded JSON
$email = isset($data->email) ? filter_var($data->email, FILTER_SANITIZE_EMAIL) : null;
$reason = isset($data->reason) ? htmlspecialchars($data->reason, ENT_QUOTES, 'UTF-8') : '';

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_response(false, 'Please provide a valid email address.', 400);
}

try {
    // Prepare and execute the database insertion
    $stmt = $conn->prepare("INSERT INTO account_deletion_requests (email, reason) VALUES (?, ?)");
    
    // Check if statement was prepared successfully
    if ($stmt === false) {
        // Log error: $conn->error
        send_response(false, 'An internal server error occurred. Please try again later.', 500);
    }
    
    $stmt->bind_param("ss", $email, $reason);

    if ($stmt->execute()) {
        // Success
        send_response(true, 'Your request has been received.');
    } else {
        // Log error: $stmt->error
        send_response(false, "We couldn't process your request at this time. Please try again.", 500);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Log error: $e->getMessage()
    send_response(false, 'A critical error occurred. Please contact support.', 500);
}

?>
