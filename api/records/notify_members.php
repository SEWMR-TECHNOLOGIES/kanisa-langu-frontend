<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Check if head_parish_id is in session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

// Check the database connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Query to get first and second member IDs from hp_group_harambee_target_information table
$sql = "SELECT first_member_id, second_member_id FROM hp_group_harambee_target_information";

// Execute the query
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Initialize an empty array to hold the member IDs
    $group_member_ids = [];

    // Loop through the results and add member IDs to the array
    while ($row = $result->fetch_assoc()) {
        $group_member_ids[] = $row['first_member_id'];
        $group_member_ids[] = $row['second_member_id'];
    }

    // Add your own member ID (assuming your custom member ID is available in a session variable or a predefined value)
    // For example, if you store your own member ID in a session variable:
    // $my_member_id = 24; 
    // $group_member_ids[] = $my_member_id;
    
    // Log all member IDs for debugging purposes
    // error_log("Group Member IDs:");
    foreach ($group_member_ids as $member_id) {
        // Log each member ID
        // error_log("Member ID: $member_id");
        
        // Get member details by member_id using the existing getMemberDetails function
        $member_result = getMemberDetails($conn, $member_id);

        // If the member result is a valid mysqli_result, fetch the data manually
        if ($member_result && $member_result->num_rows > 0) {
            $member = $member_result->fetch_assoc(); // Convert the mysqli_result to an associative array
            
            // Log the member details for debugging
            // error_log("Member Details for Member ID $member_id: " . print_r($member, true));

            // Call the SystemInfoSMS function for each member
            $response = SystemInfoSMS($conn, $member);

            if ($response === false) {
                echo json_encode(["success" => false, "message" => "Failed to send SMS for member ID $member_id"]);
                exit();
            }
        } else {
            echo json_encode(["success" => false, "message" => "No details found for member ID $member_id"]);
            exit();
        }
    }

    // Success response after processing all members
    echo json_encode(["success" => true, "message" => "SMS sent successfully to all members."]);

} else {
    echo json_encode(["success" => false, "message" => "No members found in group."]);
}

// Close the database connection
$conn->close();
?>
