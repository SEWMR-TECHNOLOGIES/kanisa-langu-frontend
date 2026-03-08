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
    $harambee_group_id = isset($_POST['harambee_group_id']) ? intval($_POST['harambee_group_id']) : 0;
    $member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;
    $target = isset($_POST['target']) ? $conn->real_escape_string($_POST['target']) : '';
    $target_amount = isset($_POST['target_amount']) ? floatval($_POST['target_amount']) : 0;
    
    // Validate inputs
    if ($harambee_group_id <= 0) {
        echo json_encode(["success" => false, "message" => "Please select a valid harambee group"]);
        exit();
    }

    if ($member_id <= 0) {
        echo json_encode(["success" => false, "message" => "Please select a valid member"]);
        exit();
    }
    
    if ($target_amount <= 0) {
        echo json_encode(["success" => false, "message" => "Please provide a valid target amount greater than zero"]);
        exit();
    }
    
    $isMrAndMrs = false;
    $mrAndMrsName = "";
    // Check for duplicate member in the specified harambee group
    $duplicateCheckQuery = "";
    $duplicateCheckMrAndMrsQuery = "";
    
    switch ($target) {
        case 'head-parish':
            $duplicateCheckQuery = "SELECT 1 FROM head_parish_harambee_group_members WHERE member_id = ? AND harambee_id = ?";
            $duplicateCheckMrAndMrsQuery = "SELECT 1 FROM head_parish_harambee_group_members WHERE (member_id = ? OR member_id = ?) AND harambee_id = ?";
            break;
        case 'sub-parish':
            $duplicateCheckQuery = "SELECT 1 FROM sub_parish_harambee_group_members WHERE member_id = ? AND harambee_id = ?";
            $duplicateCheckMrAndMrsQuery = "SELECT 1 FROM sub_parish_harambee_group_members WHERE (member_id = ? OR member_id = ?) AND harambee_id = ?";
            break;
        case 'community':
            $duplicateCheckQuery = "SELECT 1 FROM community_harambee_group_members WHERE member_id = ? AND harambee_id = ?";
            $duplicateCheckMrAndMrsQuery = "SELECT 1 FROM community_harambee_group_members WHERE (member_id = ? OR member_id = ?) AND harambee_id = ?";
            break;
        case 'group':
            $duplicateCheckQuery = "SELECT 1 FROM groups_harambee_group_members WHERE member_id = ? AND harambee_id = ?";
            $duplicateCheckMrAndMrsQuery = "SELECT 1 FROM groups_harambee_group_members WHERE (member_id = ? OR member_id = ?) AND harambee_id = ?";
            break;
        default:
            echo json_encode(["success" => false, "message" => "Invalid harambee group target"]);
            exit();
    }

   $harambee_id = getHarambeeIdFromHarambeeGroup($conn, $harambee_group_id, $target);

    // Check if harambee_id is null
    if ($harambee_id === null) {
        echo json_encode(["success" => false, "message" => "Invalid harambee group ID or target type."]);
        exit();
    }
    
    // Call hasGroupTargetType to check if the member already has a group target type
    if (hasGroupTargetType($conn, $harambee_id, $member_id, $target)) {
        $isMrAndMrs = true;
        // Get the member IDs from getMrAndMrsMembersIds function
        $mrandmrsids = getMrAndMrsMembersIds($conn, $harambee_id, $member_id, $target);
    
        if (!empty($mrandmrsids)) {
            // Assuming you want the first pair of member IDs from the result
            $first_member_id = $mrandmrsids[0]['first_member_id'];
            $second_member_id = $mrandmrsids[0]['second_member_id'];
            $mrandmrsName = $mrandmrsids[0]['group_name'];
            $mrAndMrsName = $mrandmrsName;
            $mrAndMrsStmt = $conn->prepare($duplicateCheckMrAndMrsQuery);
            $mrAndMrsStmt->bind_param("iii", $first_member_id, $second_member_id, $harambee_id);
            $mrAndMrsStmt->execute();
            $mrAndMrsStmt->store_result();

            if ($mrAndMrsStmt->num_rows > 0) {
                echo json_encode([
                    "success" => false, 
                    "message" => "A representative for $mrandmrsName has already been assigned to Harambee group. Each representative can only be added once. Please verify that either one of them is already assigned."
                ]);
                $mrAndMrsStmt->close();
                exit();
            }

            $mrAndMrsStmt->close(); // Close the statement after use
            
        } else {
            // If no members found, send an error response
            echo json_encode(["success" => false, "message" => "No members found for the given criteria"]);
            exit();
        }
    }


    
    // Prepare and execute the duplicate check
    $check_stmt = $conn->prepare($duplicateCheckQuery);
    $check_stmt->bind_param("ii", $member_id, $harambee_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Member is already assigned another harambee group"]);
        $check_stmt->close();
        exit();
    }
    $check_stmt->close();

    // Start transaction
    $conn->begin_transaction();

    // Prepare to insert if no duplicate is found
    $insert_stmt = null;
    switch ($target) {
        case 'head-parish':
            $insert_stmt = $conn->prepare("INSERT INTO head_parish_harambee_group_members (harambee_group_id, member_id, harambee_id, target_amount) VALUES (?, ?, ?, ?)");
            break;
        case 'sub-parish':
            $insert_stmt = $conn->prepare("INSERT INTO sub_parish_harambee_group_members (harambee_group_id, member_id, harambee_id, target_amount) VALUES (?, ?, ?, ?)");
            break;
        case 'community':
            $insert_stmt = $conn->prepare("INSERT INTO community_harambee_group_members (harambee_group_id, member_id, harambee_id, target_amount) VALUES (?, ?, ?, ?)");
            break;
        case 'group':
            $insert_stmt = $conn->prepare("INSERT INTO groups_harambee_group_members (harambee_group_id, member_id, harambee_id, target_amount) VALUES (?, ?, ?, ?)");
            break;
    }

    // Execute the insert statement
    if ($insert_stmt) {
        $insert_stmt->bind_param("iiis", $harambee_group_id, $member_id, $harambee_id, $target_amount);
        if ($insert_stmt->execute()) {
            $conn->commit(); // Commit transaction if successful
            
            $notificationResult = notifyMemberAssignmentBySMS($conn, $member_id, $harambee_group_id, $target, $isMrAndMrs, $mrAndMrsName);
            if ($notificationResult) {
                echo json_encode(["success" => true, "message" => "Member assigned to harambee group successfully and notified."]);
            } else {
                echo json_encode(["success" => true, "message" => "Member assigned to harambee group successfully, but notification failed."]);
            }
        } else {
            $conn->rollback(); // Rollback transaction on failure
            echo json_encode(["success" => false, "message" => "Failed to assign member to harambee group: " . $insert_stmt->error]);
        }
        $insert_stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Failed to prepare statement"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
