<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Check database connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = isset($_POST['phone']) ? $conn->real_escape_string($_POST['phone']) : '';
    $otp = isset($_POST['otp']) ? $conn->real_escape_string($_POST['otp']) : '';
    $sent_at = isset($_POST['sent_at']) ? $conn->real_escape_string($_POST['sent_at']) : '';

    // Validate required fields
    if (empty($phone) || empty($otp) || empty($sent_at)) {
        echo json_encode(["success" => false, "message" => "Phone number, OTP, and sent_at are required"]);
        exit();
    }

    $phone = preg_replace('/^0/', '', $phone); // Remove leading 0
    $phone = '255' . $phone; // Add country code '255' for Tanzania

    // Check if the OTP exists and is valid
    $otp_query = "SELECT member_id, status, TIMESTAMPDIFF(MINUTE, created_at, NOW()) AS otp_age 
                  FROM church_member_password_reset_tokens 
                  WHERE reset_token = ? AND status = 'unused'";
    $stmt = $conn->prepare($otp_query);
    $stmt->bind_param("s", $otp);
    $stmt->execute();
    $stmt->bind_result($member_id, $status, $otp_age);
    $stmt->fetch();
    $stmt->close();

    if (!$member_id) {
        echo json_encode(["success" => false, "message" => "Invalid or expired OTP"]);
        exit();
    }

    // OTP expires after 15 minutes
    if ($otp_age > 15) {
        echo json_encode(["success" => false, "message" => "OTP has expired"]);
        exit();
    }

    // Mark the OTP as used
    $update_otp_query = "UPDATE church_member_password_reset_tokens SET status = 'used' WHERE reset_token = ?";
    $stmt = $conn->prepare($update_otp_query);
    $stmt->bind_param("s", $otp);
    $stmt->execute();
    $stmt->close();

    // Return member ID if valid
    echo json_encode(["success" => true, "message" => "OTP verified successfully", "member_id" => $member_id]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
