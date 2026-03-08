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
    $member_id = isset($_GET['member_id']) ? intval($_GET['member_id']) : null;

    // Validate input
    if (!$member_id) {
        echo json_encode(["success" => false, "message" => "Missing required parameter: member_id"]);
        exit();
    }

    // Call the function to get the target amount
    $targetAmount = getEnvelopeTargetAmount($conn, $member_id);

    // Check if a target amount was returned
    if ($targetAmount === null) {
        echo json_encode(["success" => false, "message" => "No target amount found for the member"]);
        exit();
    }

    // Format the target amount
    $formattedAmount = 'TZS ' . number_format($targetAmount, 0);

    // Generate the Bootstrap div with the target amount information
    $responseDiv = '
        <div class="alert alert-info">
            Target amount for the member is: ' . $formattedAmount . '
        </div>
    ';

    // Return the HTML response
    echo json_encode([
        "success" => true,
        "html" => $responseDiv
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
