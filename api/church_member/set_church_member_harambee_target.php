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
    $target_amount = isset($_POST['target_amount']) ? floatval($_POST['target_amount']) : 0;

    // Validate the required fields
    if ($member_id === 0 || empty($target) || $harambee_id === 0 || $target_amount === 0) {
        echo json_encode(["success" => false, "message" => "member_id, target, harambee_id, and target_amount are required"]);
        exit();
    }

    // Get the current target type for this member and harambee_id
    $current_target_type = getMemberHarambeeTargetType($conn, $member_id, $target, $harambee_id);

    // Check if the target type is not 'individual'
    if ($current_target_type && $current_target_type !== 'individual') {
        echo json_encode(["success" => false, "message" => "You cannot update Mr & Mrs Harambee target."]);
        exit();
    }
    
    // Fetch the member's current target and total contributions
    $memberData = getMemberTargetAndContributions($conn, $harambee_id, $member_id, $target);
        
    if (!$memberData) {
        echo json_encode(["success" => false, "message" => "Failed to retrieve member target and contributions"]);
        exit;
    }
            
    $total_contribution = (float)$memberData['total_contribution'];
    
    // Get the current target for this member and harambee_id
    $current_target = getMemberHarambeeTarget($conn, $member_id, $target, $harambee_id);
    
    $fornatted_total_contribution = number_format($total_contribution, 0);
    if ($total_contribution >= $target_amount && $total_contribution >= $current_target) {
        echo json_encode([
            "success" => false,
            "message" => "Target amount must exceed your total harambee contributions of TZS {$fornatted_total_contribution}"
        ]);
        exit;
    }


    $formatted_current_target = number_format($current_target, 0);
    // Ensure the new target amount is greater than the current target
    if ($target_amount <= $current_target) {
        echo json_encode(["success" => false, "message" => "New target amount must be greater than the current target of TZS $formatted_current_target"]);
        exit();
    }

    // Now, insert or update the target
    $result = setMemberHarambeeTarget($conn, $member_id, $target, $harambee_id, $target_amount, 'individual');

    if ($result) {
        if ($current_target === 0) {
            sendHarambeeTargetSMS($conn, $member_id, $target_amount, $target, $harambee_id, 'individual');
            echo json_encode(["success" => true, "message" => "Target set successfully"]);
        } else {
            // Call the notification method
             $target_difference = ($total_contribution > $current_target) ? $target_amount - $total_contribution : $target_amount - $current_target;
            sendHarambeeTargetUpdateNotification($conn, $member_id, $current_target, $target_amount, $target, $harambee_id, false, null, $target_difference);
            echo json_encode(["success" => true, "message" => "Target updated successfully"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update the target"]);
    }

} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();



?>