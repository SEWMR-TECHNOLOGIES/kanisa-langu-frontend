<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Constants for configurations
define('TOKEN_EXPIRY_MINUTES', 10);

// Check database connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed. Please try again later."]);
    error_log("Database connection failed: " . $conn->connect_error);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the data from the POST request
    $phone = isset($_POST['phone']) ? $conn->real_escape_string(trim($_POST['phone'])) : '';
    $created_at = isset($_POST['created_at']) ? $conn->real_escape_string(trim($_POST['created_at'])) : '';

    // Validate the required fields
    if (empty($phone) || empty($created_at)) {
        echo json_encode(["success" => false, "message" => "Phone number and created_at are required."]);
        exit();
    }

    // Format phone number (for Tanzania: remove leading 0 and add country code 255)
    $phone = preg_replace('/^0/', '', $phone); // Remove leading 0
    $phone = '255' . $phone; // Add country code '255' for Tanzania

    // Check if the phone number exists in the church_members table
    $member_check_query = "SELECT member_id FROM church_members WHERE phone = ?";
    $stmt = $conn->prepare($member_check_query);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $stmt->bind_result($member_id);
    $stmt->fetch();
    $stmt->close();

    if (!$member_id) {
        echo json_encode(["success" => false, "message" => "Church member with the provided phone number does not exist."]);
        exit();
    }

    // Check if the phone number already exists in the church_members_accounts table
    $duplicate_check_query = "SELECT COUNT(*) FROM church_members_accounts WHERE phone = ?";
    $stmt = $conn->prepare($duplicate_check_query);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count == 0) {
        echo json_encode(["success" => false, "message" => "This phone number does not have an active account."]);
        exit();
    }

    // Delete existing tokens for the member
    $delete_tokens_query = "DELETE FROM church_member_password_reset_tokens WHERE member_id = ?";
    $stmt = $conn->prepare($delete_tokens_query);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    if ($stmt->error) {
        error_log("Failed to delete old tokens: " . $stmt->error);
        echo json_encode(["success" => false, "message" => "An error occurred while resetting your password."]);
        exit();
    }
    $stmt->close();

    // Generate a 5-digit OTP
    $otp = str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);

    // Add 10 minutes to the created_at to calculate the expiration time
    try {
        $createdAt = new DateTime($created_at);
        $createdAt->add(new DateInterval('PT' . TOKEN_EXPIRY_MINUTES . 'M'));  // Adds 10 minutes
        $token_expiry = $createdAt->format('Y-m-d H:i:s'); // Format as a string for SQL
    } catch (Exception $e) {
        error_log("Error parsing date: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Invalid created_at timestamp."]);
        exit();
    }

    // Store OTP and associate it with the member
    $insert_query = "INSERT INTO church_member_password_reset_tokens (member_id, reset_token, status, created_at, token_expiry) 
                     VALUES (?, ?, 'unused', ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("isss", $member_id, $otp, $created_at, $token_expiry);
    $stmt->execute();
    if ($stmt->error) {
        error_log("Failed to insert OTP: " . $stmt->error);
        echo json_encode(["success" => false, "message" => "Failed to generate OTP. Please try again."]);
        exit();
    }
    $stmt->close();

    // Get member details
    $member_details = getMemberDetails($conn, $member_id);
    $member = $member_details->fetch_assoc();

    // Call the sendChurchMemberOTP function
    $request_type = 'reset';
    $send_otp_status = sendChurchMemberOTP($conn, $member, $otp, $request_type);

    if ($send_otp_status) {
        echo json_encode(["success" => true, "message" => "OTP sent successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to send OTP. Please try again later."]);
        error_log("Failed to send OTP to phone: " . $phone);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
}

// Close the database connection
$conn->close();
?>
