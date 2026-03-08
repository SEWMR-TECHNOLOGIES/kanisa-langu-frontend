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
    // Get the data from the POST request
    $member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;
    $phone = isset($_POST['phone']) ? $conn->real_escape_string($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validate the required fields
    if (empty($phone) || empty($password) || $member_id === 0) {
        echo json_encode(["success" => false, "message" => "Phone number, password, and member_id are required"]);
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
    $stmt->bind_result($member_check_id);
    $stmt->fetch();
    $stmt->close();

    if (!$member_check_id || $member_check_id != $member_id) {
        echo json_encode(["success" => false, "message" => "Church member with the provided phone number does not exist or does not match member_id"]);
        exit();
    }

    // Hash the password before saving it
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check if an account for this member already exists
    $account_check_query = "SELECT COUNT(*) FROM church_members_accounts WHERE member_id = ?";
    $stmt = $conn->prepare($account_check_query);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    // If account already exists, return an error
    if ($count > 0) {
        echo json_encode(["success" => false, "message" => "Church member already has an active account"]);
        exit();
    }

    // Insert the new password record into the church_members_accounts table
    $insert_query = "INSERT INTO church_members_accounts (member_id, phone, member_password, created_at, updated_at) 
                     VALUES (?, ?, ?, NOW(), NOW())";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iss", $member_id, $phone, $hashed_password);
    
    if ($stmt->execute()) {
        // Get member details after success
        $member_details = getMemberDetails($conn, $member_id);
        $member = $member_details->fetch_assoc();
        
        // Remove the "USHARIKA WA" prefix from the head_parish_name if present
        if (isset($member['head_parish_name'])) {
            $member['head_parish_name'] = str_replace("USHARIKA WA ", "", $member['head_parish_name']);
        }
    
        // Return success along with member details
        echo json_encode([
            "success" => true,
            "message" => "Account Created Successfully",
            "member" => $member
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to set the password"]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
