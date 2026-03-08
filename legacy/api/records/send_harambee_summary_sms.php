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

    // Log the received data for debugging
    error_log("Received POST data: " . json_encode($_POST));

    // Validate input
    if (!$harambee_id || !$member_id || !$target) {
        error_log("Missing required parameters: harambee_id, member_id, or target");
        echo json_encode(["success" => false, "message" => "Missing required parameters"]);
        exit();
    }

    // Call the function to get member target and contributions
    $memberContributions = getMemberTargetAndContributions($conn, $harambee_id, $member_id, $target);

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
        // Prepare the message for each member
        $name_or_group = $member['group_name'] ?? $member['first_name'];
        $is_group = $member['group_name'] !== null;
        $balance_text = ($balance < 0) ? 'Zidio' : 'Salio';
        // Get SMS credentials for the member
        $smsInfo = get_sms_credentials($conn, $member['head_parish_id']);
    
        // Check if credentials were retrieved successfully
        if (!$smsInfo) {
            error_log("No SMS credentials found for member ID: " . $member['member_id']);
            return false; // No credentials found
        }
    
        // Obtain Accountant Info
        $identifier_type = 'harambee';
        $accountant_name = '';
        $accountant_phone = '';
        $accountantDetails = getHarambeeAccountant($conn, $target, $harambee_id, $identifier_type);
        // Check if the function returned valid results
        if ($accountantDetails) {
            $accountant_name = $accountantDetails['first_name'];
            $accountant_phone =  $accountantDetails['phone'];
        }
        
        $message = "Shalom " . htmlspecialchars($name_or_group) . ",\n"
            . "Mhutasari wa Harambee:\n"
            . "Ahadi: Shs. " . number_format($target_amount, 0) . "\n"
            . "Taslimu: Shs. " . number_format($total_contribution, 0) . "\n"
            . "$balance_text: Shs. " . number_format(abs($balance), 0) . "\n"
            . ($is_group ? "Mungu awabariki" : "Mungu akubariki") ."\nM/Hazina\n$accountant_phone";
        
        // end SMS using Quick Send
        $smsClient = new SewmrSMSClient($smsInfo['api_token'], $smsInfo['sender_id']);
        $response = $smsClient->sendQuickSMS(null, $message, [$member['phone']]);

        if (!isset($response['success']) || $response['success'] !== true) {
            $errorMessage = $response['message'] ?? "Unknown error";
            error_log("Failed to send SMS to {$member['phone']}: $errorMessage");
            return false;
        }

    }

    // Prepare the JSON response with formatted data
    $response = [
        "success" => true,
        "message" => "Harambee message sent successfully!"
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
