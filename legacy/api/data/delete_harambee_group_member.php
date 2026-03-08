<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    $harambee_group_id = isset($input['harambee_group_id']) ? intval($input['harambee_group_id']) : 0;
    $member_id = isset($input['member_id']) ? intval($input['member_id']) : 0;
    $target = isset($input['target']) ? $input['target'] : '';

    // Validate inputs
    if ($harambee_group_id <= 0 || $member_id <= 0 || empty($target)) {
        echo json_encode(["success" => false, "message" => "Group ID, Member ID, and Target are required"]);
        exit();
    }

    // Prepare SQL query to delete the member from the appropriate group table
    $query = "";
    
    switch ($target) {
        case 'head-parish':
            $query = "
                DELETE FROM 
                    head_parish_harambee_group_members 
                WHERE 
                    harambee_group_id = ? AND member_id = ?
            ";
            break;

        case 'sub-parish':
            $query = "
                DELETE FROM 
                    sub_parish_harambee_group_members 
                WHERE 
                    harambee_group_id = ? AND member_id = ?
            ";
            break;

        case 'community':
            $query = "
                DELETE FROM 
                    community_harambee_group_members 
                WHERE 
                    harambee_group_id = ? AND member_id = ?
            ";
            break;

        case 'group':
            $query = "
                DELETE FROM 
                    groups_harambee_group_members 
                WHERE 
                    harambee_group_id = ? AND member_id = ?
            ";
            break;

        default:
            echo json_encode(["success" => false, "message" => "Invalid target specified"]);
            exit();
    }

    // Prepare the statement
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ii", $harambee_group_id, $member_id);
        
        // Execute the statement
        if ($stmt->execute()) {
            // Check if any rows were affected (deleted)
            if ($stmt->affected_rows > 0) {
                echo json_encode(["success" => true, "message" => "Member removed successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "Member not found in this group"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Error executing query: " . $stmt->error]);
        }

        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Failed to prepare statement: " . $conn->error]);
    }

    // Close the connection
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
