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
    $phone = isset($_POST['phone']) ? $conn->real_escape_string($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validate the required fields
    if (empty($phone) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Phone number and password are required"]);
        exit();
    }

    // Format phone number (for Tanzania: remove leading 0 and add country code 255)
    $phone = preg_replace('/^0/', '', $phone); // Remove leading 0
    $phone = '255' . $phone; // Add country code '255' for Tanzania

    // Check if the phone number exists in the church_members table
    $member_check_query = "SELECT member_id, member_password FROM church_members_accounts WHERE phone = ?";
    $stmt = $conn->prepare($member_check_query);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $stmt->bind_result($member_id, $hashed_password);
    $stmt->fetch();
    $stmt->close();

    // If no member is found, respond with a generic "incorrect username or password"
    if (!$member_id) {
        echo json_encode(["success" => false, "message" => "Incorrect username or password"]);
        exit();
    }

    // Verify the password
    if (!password_verify($password, $hashed_password)) {
        echo json_encode(["success" => false, "message" => "Incorrect username or password"]);
        exit();
    }

    // Get member details after successful login
    $member_details = getMemberDetails($conn, $member_id);
    $member = $member_details->fetch_assoc();

    // Remove the "USHARIKA WA" prefix from the head_parish_name if present
    if (isset($member['head_parish_name'])) {
        $member['head_parish_name'] = str_replace("USHARIKA WA ", "", $member['head_parish_name']);
    }
    // Return success along with member details
    echo json_encode([
        "success" => true,
        "message" => "Login Successful",
        "member" => $member
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
