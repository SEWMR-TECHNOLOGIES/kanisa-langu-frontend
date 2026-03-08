<?php
// Include the database connection file
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
// Ensure content type is JSON
header('Content-Type: application/json');

// Set timezone to Africa/Nairobi
date_default_timezone_set('Africa/Nairobi');

// Allow from any origin and set headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST");


// Check if data is sent via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

// Validate member_id
if (empty($data['member_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Member ID is required']);
    exit;
}

$member_id = $data['member_id'];

// Delete token(s) for the given member_id
$sqlDelete = "DELETE FROM church_member_fcm_tokens WHERE member_id = ?";
$stmtDelete = $conn->prepare($sqlDelete);
$stmtDelete->bind_param("i", $member_id);

if ($stmtDelete->execute()) {
    echo json_encode(['success' => true, 'message' => 'Token deleted successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete token']);
}

// Close the connection
$conn->close();
?>
