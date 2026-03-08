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

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $target = isset($_POST['target']) ? $conn->real_escape_string($_POST['target']) : null;
    $harambee_id = isset($_POST['harambee_id']) ? $conn->real_escape_string($_POST['harambee_id']) : null;
    $shared_group_id = isset($_POST['shared_harambee_group_id']) ? $conn->real_escape_string($_POST['shared_harambee_group_id']) : null;
    $new_target_amount = isset($_POST['new_target_amount']) ? floatval($_POST['new_target_amount']) : 0.00;
    $target_table = '';
    $group_table  = '';

    // Validate mandatory fields
    if (empty($target)) {
        echo json_encode(["success" => false, "message" => "Select a valid target."]);
        exit();
    }

    if (empty($harambee_id)) {
        echo json_encode(["success" => false, "message" => "Please select Harambee"]);
        exit();
    }

    // Ensure the group name is mandatory if handling group targets
    if (empty($shared_group_id)) {
        echo json_encode(["success" => false, "message" => "Please select a valid shared harambee group"]);
        exit();
    }

    if ($new_target_amount <= 0) {
        echo json_encode(["success" => false, "message" => "New Target amount is required"]);
        exit();
    }

    // Dynamically set the table names
    switch ($target) {
        case 'head-parish':
            $target_table = 'head_parish_harambee_targets';
            $group_table = 'hp_group_harambee_target_information';
            break;
        case 'sub-parish':
            $target_table = 'sub_parish_harambee_targets';
            $group_table = 'sp_group_harambee_target_information';
            break;
        case 'community':
            $target_table = 'community_harambee_targets';
            $group_table = 'com_group_harambee_target_information';
            break;
        case 'groups':
            $target_table = 'groups_harambee_targets';
            $group_table = 'gp_group_harambee_target_information';
            break;
        default:
            echo json_encode(["success" => false, "message" => "Invalid target table"]);
            exit();
    }

    // Get the member IDs from getMrAndMrsMembersIds function
    $mrandmrsids = getMrAndMrsMembersIdsFromGroupId($conn, $harambee_id, $shared_group_id, $target);

    if (!empty($mrandmrsids)) {
        // Assuming you want the first pair of member IDs from the result
        $first_member_id = $mrandmrsids[0]['first_member_id'];
        $second_member_id = $mrandmrsids[0]['second_member_id'];
        $mrandmrsName = $mrandmrsids[0]['group_name'];
        
        // Fetch member target and contributions
        $memberData = getMemberTargetAndContributions($conn, $harambee_id, $first_member_id, $target);
        
        if (!$memberData) {
            echo json_encode(["success" => false, "message" => "Failed to retrieve member target and contributions"]);
            exit;
        }

        $total_contribution = (float)$memberData['total_contribution'];
        $formatted_total_contribution = number_format($total_contribution, 0);
        // Get the current target for the member
        $current_target = getMemberHarambeeTarget($conn, $first_member_id, $target, $harambee_id);
        
        // Check if the target exceeds total contributions
        if ($total_contribution >= $new_target_amount && $total_contribution >= $current_target) {
            echo json_encode([
                "success" => false,
                "message" => "Target amount must exceed your total Harambee contributions of TZS {$formatted_total_contribution}"
            ]);
            exit;
        }

        $formatted_current_target = number_format($current_target, 0);

        // Ensure the new target is greater than the current target
        if ($new_target_amount <= $current_target) {
            echo json_encode([
                "success" => false,
                "message" => "New target amount must be greater than the current target of TZS {$formatted_current_target}"
            ]);
            exit;
        }
        $target_difference = ($total_contribution > $current_target) ? $new_target_amount - $total_contribution : $new_target_amount - $current_target;
        // Insert or update target
        $result = setMemberHarambeeTarget($conn, $first_member_id, $target, $harambee_id, $new_target_amount,'group'); //for first member 
        $result = setMemberHarambeeTarget($conn, $second_member_id, $target, $harambee_id, $new_target_amount,'group'); // for second member
        
        sendHarambeeTargetUpdateNotification($conn, $first_member_id, $current_target, $new_target_amount, $target, $harambee_id,true,$mrandmrsName, $target_difference);
        sendHarambeeTargetUpdateNotification($conn, $second_member_id, $current_target, $new_target_amount, $target, $harambee_id,true,$mrandmrsName, $target_difference);
    } else {
        // If no members found, send an error response
        echo json_encode(["success" => false, "message" => "No members found for the given criteria"]);
        exit();
    }

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Update the group target instead of insert
        $sql_group = "UPDATE $group_table SET target = ? WHERE harambee_id = ? AND id = ?";
        $stmt_group = $conn->prepare($sql_group);
        $stmt_group->bind_param("dii", $new_target_amount, $harambee_id, $shared_group_id);
        $stmt_group->execute();
        
        // Commit the transaction
        $conn->commit();
        echo json_encode(["success" => true, "message" => "Target updated successfully"]);
    } catch (Exception $e) {
        $conn->rollback(); // Rollback the transaction
        echo json_encode(["success" => false, "message" => "Failed to update target: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
