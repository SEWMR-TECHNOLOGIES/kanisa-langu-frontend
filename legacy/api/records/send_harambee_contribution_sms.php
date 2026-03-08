<?php
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Check if the database connection is successful
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the required parameters from form data
    $harambee_id = isset($_POST['harambee_id']) ? intval($_POST['harambee_id']) : null;
    $member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : null;
    $target = isset($_POST['target']) ? $_POST['target'] : null;
    $contribution_date = isset($_POST['contribution_date']) ? $_POST['contribution_date'] : null;

    // Log the received data for debugging
    // error_log("Received POST data: " . json_encode($_POST));

    // Validate input
    if (!$harambee_id || !$member_id || !$target || !$contribution_date) {
        error_log("Missing required parameters: harambee_id, member_id, or target");
        echo json_encode(["success" => false, "message" => "Missing required parameters"]);
        exit();
    }

    // Call the function to get member target and contributions
    $memberContributions = getMemberTargetAndContributions($conn, $harambee_id, $member_id, $target);
    
    $contribution_table = null;
    $target_table = null;
    
    switch ($target) {
        case 'head-parish':
            $contribution_table = 'head_parish_harambee_contribution';
            $target_table = 'head_parish_harambee_targets';
            break;
        case 'sub-parish':
            $contribution_table = 'sub_parish_harambee_contribution';
            $target_table = 'sub_parish_harambee_targets';
            break;
        case 'community':
            $contribution_table = 'community_harambee_contribution';
            $target_table = 'community_harambee_targets';
            break;
        case 'groups':
            $contribution_table = 'groups_harambee_contribution';
            $target_table = 'groups_harambee_targets';
            break;
        default:
            throw new Exception("Invalid target specified.");
    }
    
    $on_date_contributions = getTotalContributionsOnDate($conn, $harambee_id, $member_id, $contribution_date, $contribution_table);
    $contribution = $on_date_contributions['contribution'];
    // Log the member contribution details for debugging
    error_log("Fetched member contribution details: " . json_encode($memberContributions));

    if ($memberContributions === false) {
        error_log("Invalid target or unable to fetch details for harambee_id: $harambee_id, member_id: $member_id, target: $target");
        echo json_encode(["success" => false, "message" => "Invalid target or unable to fetch details"]);
        exit();
    }

            
    // Extract target and contribution
    $target_amount = $memberContributions['target_amount'];
    $total_contribution = $memberContributions['total_contribution'];

    // Calculate balance based on the provided logic
    $balance = ($target_amount == 0 && $total_contribution > 0) ? 0 : $target_amount - $total_contribution;

    // Get details from the `getMemberHarambeeDetails` function
    $memberDetails = getMemberHarambeeDetails($conn, $member_id, $harambee_id, $target);

    // Check if the function returned valid data
    if (empty($memberDetails['members'])) {
        echo json_encode(["success" => false, "message" => "No members found for the provided details"]);
        exit();
    }

    // Process the member details to extract phone numbers and group names/first names
    $processedMembers = [];
    foreach ($memberDetails['members'] as $member) {
        $total_contribution = calculateTotalContributions($conn, $member['member_id'], $harambee_id, $contribution_table)->fetch_assoc()['total_contribution'] ?? 0;
        $member['total_contribution'] = $total_contribution;
        $phone = $member['phone'];
    
        // Log or handle the response
        if (!sendHarambeeContributionSMS($conn, $contribution, $member, $contribution_date, $target, $harambee_id)) {
            $errorMessage = $responseDecoded['message'] ?? "Unknown error";
            error_log("Failed to send SMS to {$phone}: $errorMessage");
            return false; // Return false if SMS failed
        }

    }

    // Prepare the JSON response with formatted data
    $response = [
        "success" => true,
        "message" => "Harambee contribution message sent successfully!"
    ];

    // Log the response data for debugging
    error_log("Response data: " . json_encode($response));

    echo json_encode($response);
} else {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
