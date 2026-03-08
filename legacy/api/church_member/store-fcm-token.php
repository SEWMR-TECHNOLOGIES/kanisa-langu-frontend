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

// Validate required fields
if (empty($data['member_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Member ID is required']);
    exit;
}
if (empty($data['head_parish_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Head Parish ID is required']);
    exit;
}
if (empty($data['token'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'FCM token is required']);
    exit;
}

$member_id = $data['member_id'];
$head_parish_id = $data['head_parish_id'];
$fcm_token = $data['token'];

$now = date('Y-m-d H:i:s'); // manual timestamp

// Check if token exists for this member_id and head_parish_id
$sqlCheck = "SELECT * FROM church_member_fcm_tokens WHERE member_id = ? AND head_parish_id = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("ii", $member_id, $head_parish_id);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if ($resultCheck->num_rows > 0) {
    // Update the existing token and updated_at
    $sqlUpdate = "UPDATE church_member_fcm_tokens SET fcm_token = ?, updated_at = ? WHERE member_id = ? AND head_parish_id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ssii", $fcm_token, $now, $member_id, $head_parish_id);

    if ($stmtUpdate->execute()) {
        echo json_encode(['success' => true, 'message' => 'Token updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update token']);
    }
} else {
    // Insert new token record
    $sqlInsert = "INSERT INTO church_member_fcm_tokens (member_id, head_parish_id, fcm_token, created_at, updated_at) VALUES (?, ?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("iisss", $member_id, $head_parish_id, $fcm_token, $now, $now);

    if ($stmtInsert->execute()) {
        echo json_encode(['success' => true, 'message' => 'Token added successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to add token']);
    }
}

// Close the connection
$conn->close();
