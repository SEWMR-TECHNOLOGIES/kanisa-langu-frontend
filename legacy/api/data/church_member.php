<?php
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Check if the database connection is successful
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Ensure it's a GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Retrieve the required parameters
    $member_id = isset($_GET['id']) ? intval($_GET['id']) : null;

    // Validate input
    if (!$member_id) {
        echo json_encode(["success" => false, "message" => "Missing required parameter: id"]);
        exit();
    }

    // Call the function to get member details
    $result = getMemberDetails($conn, $member_id);

    // Check if the member exists
    if ($result->num_rows > 0) {
        $member = $result->fetch_assoc(); // Fetch the member details as an associative array
        echo json_encode(["success" => true, "data" => $member]);
    } else {
        echo json_encode(["success" => false, "message" => "Member not found"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
