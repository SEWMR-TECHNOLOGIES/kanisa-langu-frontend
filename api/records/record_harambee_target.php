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
    $target_type = isset($_POST['target_type']) ? $conn->real_escape_string($_POST['target_type']) : 'individual';
    $harambee_id = isset($_POST['harambee_id']) ? $conn->real_escape_string($_POST['harambee_id']) : null;
    $target_amount = isset($_POST['target']) ? floatval($_POST['target']) : 0.00;
    $group_name = isset($_POST['group_name']) ? $conn->real_escape_string($_POST['group_name']) : null;
    $target = isset($_POST['target_table']) ? $conn->real_escape_string($_POST['target_table']) : null;

    // Validate mandatory fields separately
    if (empty($harambee_id)) {
        echo json_encode(["success" => false, "message" => "Please select Harambee"]);
        exit();
    }

    if ($target_amount <= 0) {
        echo json_encode(["success" => false, "message" => "Target must be greater than 0."]);
        exit();
    }

    if (empty($target)) {
        echo json_encode(["success" => false, "message" => "Target table is required."]);
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

    // For individual target
    if ($target_type === 'individual') {
        $member_id = isset($_POST['member_id']) ? $conn->real_escape_string($_POST['member_id']) : 0;
        $member_harambee_responsibility = isset($_POST['member_harambee_responsibility']) ? $conn->real_escape_string($_POST['member_harambee_responsibility']) : null;

        if ($member_id <= 0) {
            echo json_encode(["success" => false, "message" => "Please select church member"]);
            exit();
        }

        // Get sub_parish_id and community_id from POST request or fetch them using member_id
        $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : null;
        $community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : null;

        if (!$sub_parish_id || !$community_id) {
            $location_data = getSubParishAndCommunity($member_id, $conn);
            $sub_parish_id = $location_data['sub_parish_id'];
            $community_id = $location_data['community_id'];
        }

        if (!$sub_parish_id || !$community_id) {
            echo json_encode(["success" => false, "message" => "Sub Parish ID and Community ID are required and could not be found"]);
            exit();
        }

        // Check if the member has an existing target for the provided harambee_id
        $stmt = $conn->prepare("SELECT target FROM $target_table WHERE member_id = ? AND harambee_id = ?");
        $stmt->bind_param("is", $member_id, $harambee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $current_target = (float)$row['target'];
        
            // Fetch the member's current target and total contributions
            $memberData = getMemberTargetAndContributions($conn, $harambee_id, $member_id, $target);
        
            if (!$memberData) {
                echo json_encode(["success" => false, "message" => "Failed to retrieve member target and contributions"]);
                exit;
            }
            
            $total_contribution = (float)$memberData['total_contribution'];
            
            $fornatted_total_contribution = number_format($total_contribution, 0);
            if ($total_contribution >= $target_amount && $total_contribution >= $current_target) {
                echo json_encode([
                    "success" => false,
                    "message" => "Target amount must exceed your total harambee contributions of {$fornatted_total_contribution}"
                ]);
                exit;
            }
        
            $target_difference = ($total_contribution > $current_target) ? $target_amount - $total_contribution : $target_amount - $current_target;
            // Validate the new target
            if ($target_amount >= $current_target || $_SESSION['head_parish_admin_role'] == 'admin') {
                // Update the target
                $sql_update = "UPDATE $target_table SET target = ? WHERE member_id = ? AND harambee_id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("dis", $target_amount, $member_id, $harambee_id);
        
                if ($stmt_update->execute()) {
                    // Call the notification method
                    sendHarambeeTargetUpdateNotification($conn, $member_id, $current_target, $target_amount, $target, $harambee_id,false, null, $target_difference);
                    echo json_encode(["success" => true, "message" => "Individual target updated successfully"]);
                } else {
                    echo json_encode(["success" => false, "message" => "Failed to update individual target"]);
                }
        
                $stmt_update->close();
            } else {
                // New target is not greater than the current target
                echo json_encode(["success" => false, "message" => "No changes made to your Harambee target. New Target is less or equal to current target"]);
            }
        } else {
            // Insert the individual target if none exists
            $sql_insert = "INSERT INTO $target_table (member_id, target, target_type, head_parish_id, sub_parish_id, community_id, harambee_committee_responsibility, harambee_id) 
                           VALUES (?, ?, 'individual', ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("idiiiss", $member_id, $target_amount, $head_parish_id, $sub_parish_id, $community_id, $member_harambee_responsibility, $harambee_id);
            if ($stmt_insert->execute()) {
                sendHarambeeTargetSMS($conn, $member_id, $target_amount, $target, $harambee_id, $target_type);
                echo json_encode(["success" => true, "message" => "Individual target recorded successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to record individual target"]);
            }
            $stmt_insert->close();
        }

    // For group target
    } elseif ($target_type === 'group') {
        $first_member_id = isset($_POST['first_member_id']) ? $conn->real_escape_string($_POST['first_member_id']) : 0;
        $second_member_id = isset($_POST['second_member_id']) ? $conn->real_escape_string($_POST['second_member_id']) : 0;
        $first_member_harambee_responsibility = isset($_POST['first_member_harambee_responsibility']) ? $conn->real_escape_string($_POST['first_member_harambee_responsibility']) : null;
        $second_member_harambee_responsibility = isset($_POST['second_member_harambee_responsibility']) ? $conn->real_escape_string($_POST['second_member_harambee_responsibility']) : null;

        // Ensure the group name is mandatory
        if (empty($group_name)) {
            echo json_encode(["success" => false, "message" => "Group name is required for group targets."]);
            exit();
        }
    
        // Ensure the group name is unique for the given Harambee ID
        $stmt = $conn->prepare("SELECT * FROM $group_table WHERE group_name = ? AND harambee_id = ?");
        $stmt->bind_param("si", $group_name, $harambee_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            echo json_encode(["success" => false, "message" => "Choose a different Group name for this Harambee. $group_name is already taken."]);
            exit();
        }
        
       if ($first_member_id <= 0) {
            echo json_encode(["success" => false, "message" => "The First Member is required to create a group target."]);
            exit();
        }

        if ($second_member_id <= 0) {
            echo json_encode(["success" => false, "message" => "The Second Member is required to create a group target."]);
            exit();
        }

        if ($first_member_id === $second_member_id) {
            echo json_encode(["success" => false, "message" => "The First and Second Member must refer to two distinct church members."]);
            exit();
        }

        // Check if any group member has prior targets
        $stmt = $conn->prepare("SELECT * FROM $target_table WHERE (member_id = ? OR member_id = ?) AND harambee_id = ?");
        $stmt->bind_param("iis", $first_member_id, $second_member_id, $harambee_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(["success" => false, "message" => "One or more group members already have prior Harambee targets"]);
            exit();
        }

        // Begin transaction for group
        $conn->begin_transaction();

        try {
            // Insert group target
            $sql_group = "INSERT INTO $group_table (first_member_id, second_member_id, target, group_name, harambee_id) 
                          VALUES (?, ?, ?, ?, ?)";
            $stmt_group = $conn->prepare($sql_group);
            $stmt_group->bind_param("iidsi", $first_member_id, $second_member_id, $target_amount, $group_name, $harambee_id);
            $stmt_group->execute();

            // Insert individual members' targets
            $sql_individual = "INSERT INTO $target_table (member_id, target, target_type, head_parish_id, sub_parish_id, community_id, harambee_committee_responsibility, harambee_id) 
                               VALUES (?, ?, 'group', ?, ?, ?, ?, ?)";

            // First member
            $location_data_1 = getSubParishAndCommunity($first_member_id, $conn);
            $sub_parish_id_1 = $location_data_1['sub_parish_id'];
            $community_id_1 = $location_data_1['community_id'];
            $stmt_individual = $conn->prepare($sql_individual);
            $stmt_individual->bind_param("idiiiss", $first_member_id, $target_amount, $head_parish_id, $sub_parish_id_1, $community_id_1, $first_member_harambee_responsibility, $harambee_id);
            $stmt_individual->execute();

            // Second member
            $location_data_2 = getSubParishAndCommunity($second_member_id, $conn);
            $sub_parish_id_2 = $location_data_2['sub_parish_id'];
            $community_id_2 = $location_data_2['community_id'];
            $stmt_individual->bind_param("idiiiss", $second_member_id, $target_amount, $head_parish_id, $sub_parish_id_2, $community_id_2, $second_member_harambee_responsibility, $harambee_id);
            $stmt_individual->execute();

            // Commit the transaction
            $conn->commit();
            sendHarambeeTargetSMS($conn, $first_member_id, $target_amount, $target, $harambee_id, $target_type, $group_name);
            sendHarambeeTargetSMS($conn, $second_member_id, $target_amount, $target, $harambee_id, $target_type, $group_name);
            echo json_encode(["success" => true, "message" => "Group target recorded successfully"]);
        } catch (Exception $e) {
            $conn->rollback(); // Rollback the transaction
            echo json_encode(["success" => false, "message" => "Failed to record group target: " . $e->getMessage()]);
        }
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
