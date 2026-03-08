<?php
require_once('../config/db_connection.php');
require_once('../utils/encryption_functions.php'); 

/**
 * Verify the reset code from the URL and manage sessions.
 *
 * @param string $resetCodeUrl Encoded reset code from the URL.
 * @return array Response with success status, message, and verification flag.
 */
function verifyResetCode($resetCodeUrl) {
    global $conn; // Ensure $conn is accessible in this function
    
    // URL decode the reset code
    $encryptedCode = urldecode($resetCodeUrl);
    
    // Decrypt the code
    $resetCode = decryptData($encryptedCode);

    // Prepare response array
    $response = [
        "success" => false,
        "message" => "Invalid or expired reset code.",
        "verified" => false
    ];

    // Check the reset code in the database
    $sql = "SELECT admin_id, admin_type, request_time, expiration_time, used 
            FROM admin_password_reset_codes 
            WHERE reset_code = ? AND used = FALSE";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $resetCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $resetRecord = $result->fetch_assoc();

        // Check if the reset code is expired
        if ($resetRecord['expiration_time'] === NULL || new DateTime() <= new DateTime($resetRecord['expiration_time'])) {
            // Reset code is valid
            session_start(); // Start session
            
            // Set reset-specific session variables
            $_SESSION['reset_admin_id'] = $resetRecord['admin_id'];
            $_SESSION['reset_admin_type'] = $resetRecord['admin_type'];
            
            // Update response
            $response["success"] = true;
            $response["message"] = "Reset code is valid.";
            $response["verified"] = true; 
            $response["data"] = $resetRecord;
        } else {
            $response["message"] = "Reset code has expired.";
        }
    }

    $stmt->close();

    return $response;
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resetCodeUrl = isset($_POST['code']) ? $_POST['code'] : '';

    if (!empty($resetCodeUrl)) {
        $verificationResult = verifyResetCode($resetCodeUrl);
        echo json_encode($verificationResult);
    } else {
        echo json_encode(["success" => false, "message" => "Reset code is missing.", "verified" => false]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method.", "verified" => false]);
}
?>
