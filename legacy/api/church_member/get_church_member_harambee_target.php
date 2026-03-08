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
    $target = isset($_POST['target']) ? $conn->real_escape_string($_POST['target']) : '';
    $harambee_id = isset($_POST['harambee_id']) ? intval($_POST['harambee_id']) : 0;

    // Validate the required fields
    if ($member_id === 0 || empty($target) || $harambee_id === 0) {
        echo json_encode(["success" => false, "message" => "member_id, target, and harambee_id are required"]);
        exit();
    }

    // Fetch the member's Harambee target for the given Harambee ID
    $target_amount = (int) getMemberHarambeeTarget($conn, $member_id, $target, $harambee_id);

    // Always return the target amount, even if it's 0
    echo json_encode([
        "success" => true,
        "target_amount" => $target_amount
    ]);

} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
