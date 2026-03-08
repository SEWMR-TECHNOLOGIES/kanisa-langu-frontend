<?php
require_once('../config/db_connection.php');

/**
 * Save the reset code to the database.
 *
 * @param int $adminId The ID of the admin.
 * @param string $resetCode The encrypted reset code.
 * @param string $adminType The type of admin ('kanisalangu_admin', 'diocese_admin', 'province_admin', 'head_parish_admin').
 * @param string $clientTime The client-side timestamp when the request was made.
 * @return void
 */
function saveAdminResetCode($adminId, $resetCode, $adminType, $clientTime) {
    global $conn;
    
    // Use the client time as request time
    $requestTime = $clientTime;
    
    // Set expiration time to 1 hour from request time
    $expiryTime = date('Y-m-d H:i:s', strtotime('+1 hour', strtotime($requestTime))); 
    
    // Prepare the SQL statement to insert the reset code
    $sql = "INSERT INTO admin_password_reset_codes (admin_id, admin_type, reset_code, request_time, expiration_time) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        // Handle SQL statement preparation failure
        throw new Exception("SQL statement preparation failed: " . $conn->error);
    }

    // Bind the parameters (admin ID, admin type, reset code, request time, and expiration time)
    $stmt->bind_param("issss", $adminId, $adminType, $resetCode, $requestTime, $expiryTime);
    
    // Execute the SQL statement
    if (!$stmt->execute()) {
        // Handle execution failure
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }

    // Close the statement
    $stmt->close();
}
?>
