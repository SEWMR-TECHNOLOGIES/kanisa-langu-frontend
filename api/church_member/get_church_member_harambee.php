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
    error_log($target);
    // Validate the required fields
    if ($member_id === 0 || empty($target)) {
        echo json_encode(["success" => false, "message" => "member_id and target are required"]);
        exit();
    }

    // Fetch Harambee IDs for the member
    $harambee_ids = getMemberHarambee($conn, $member_id, $target);

    // Initialize an empty array to store the Harambee details
    $harambee_details = [];

    // If no Harambee IDs are found, return an empty array
    if ($harambee_ids) {
        // Fetch the Harambee details for each Harambee ID
        foreach ($harambee_ids as $harambee_id) {
            $details = getHarambeeDetailsByTarget($conn, $harambee_id, $target);
            if ($details) {
                
                $details['amount'] = number_format((float)$details['amount'], 0);
                // Append the target as 'category' to each Harambee detail
                $details['category'] = $target;
                // Add the modified details to the harambee_details array
                $harambee_details[] = $details;
            }
        }
    }

    // Return the result as a JSON response
    echo json_encode([
        "success" => true,
        "message" => "Harambee details fetched successfully",
        "harambee_details" => $harambee_details // This will be an empty array if no details found
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
