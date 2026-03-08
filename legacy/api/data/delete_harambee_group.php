<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    $harambee_group_id = isset($input['harambee_group_id']) ? intval($input['harambee_group_id']) : 0;
    $target = isset($input['target']) ? $input['target'] : '';

    // Validate input
    if ($harambee_group_id <= 0 || empty($target)) {
        echo json_encode(["success" => false, "message" => "Group ID and Target are required"]);
        exit();
    }

    // Prepare SQL queries for deletion based on target
    $queryDeleteGroup = "";
    $queryDeleteMembers = "";

    switch ($target) {
        case 'head-parish':
            $queryDeleteGroup = "
                DELETE FROM 
                    head_parish_harambee_groups 
                WHERE 
                    harambee_group_id = ?
            ";
            $queryDeleteMembers = "
                DELETE FROM 
                    head_parish_harambee_group_members 
                WHERE 
                    harambee_group_id = ?
            ";
            break;

        case 'sub-parish':
            $queryDeleteGroup = "
                DELETE FROM 
                    sub_parish_harambee_groups 
                WHERE 
                    harambee_group_id = ?
            ";
            $queryDeleteMembers = "
                DELETE FROM 
                    sub_parish_harambee_group_members 
                WHERE 
                    harambee_group_id = ?
            ";
            break;

        case 'community':
            $queryDeleteGroup = "
                DELETE FROM 
                    community_harambee_groups 
                WHERE 
                    harambee_group_id = ?
            ";
            $queryDeleteMembers = "
                DELETE FROM 
                    community_harambee_group_members 
                WHERE 
                    harambee_group_id = ?
            ";
            break;

        case 'group':
            $queryDeleteGroup = "
                DELETE FROM 
                    groups_harambee_groups 
                WHERE 
                    harambee_group_id = ?
            ";
            $queryDeleteMembers = "
                DELETE FROM 
                    groups_harambee_group_members 
                WHERE 
                    harambee_group_id = ?
            ";
            break;

        default:
            echo json_encode(["success" => false, "message" => "Invalid target specified"]);
            exit();
    }

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Delete members first
        if ($stmt = $conn->prepare($queryDeleteMembers)) {
            $stmt->bind_param("i", $harambee_group_id);
            $stmt->execute();
            $stmt->close();
        }

        // Now delete the group
        if ($stmt = $conn->prepare($queryDeleteGroup)) {
            $stmt->bind_param("i", $harambee_group_id);
            $stmt->execute();
            $stmt->close();
        }

        // Commit transaction
        $conn->commit();
        echo json_encode(["success" => true, "message" => "Group and its members deleted successfully"]);
    } catch (Exception $e) {
        // Rollback transaction in case of an error
        $conn->rollback();
        echo json_encode(["success" => false, "message" => "Failed to delete group: " . $e->getMessage()]);
    }

    // Close the connection
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
