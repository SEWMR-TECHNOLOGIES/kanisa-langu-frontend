<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $target = isset($_GET['target']) ? $_GET['target'] : '';
    $harambee_id = isset($_GET['harambee_id']) ? intval($_GET['harambee_id']) : 0;
    $harambee_group_id = isset($_GET['harambee_group_id']) ? intval($_GET['harambee_group_id']) : 0; // New parameter
    $sub_parish_id = isset($_GET['sub_parish_id']) ? intval($_GET['sub_parish_id']) : null;
    // Validate inputs
    if (empty($target) || $harambee_id <= 0) {
        echo json_encode(["success" => false, "message" => "Target and Harambee ID are required"]);
        exit();
    }

    // Prepare SQL query based on the target
    $query = "";
    $params = [$harambee_id];
    $paramTypes = "i"; // Initialize parameter type for binding

    // Check if harambee_group_id is provided
    if ($harambee_group_id > 0) {
        $queryCondition = " AND h.harambee_group_id = ?";
        $params[] = $harambee_group_id;
        $paramTypes .= "i"; // Append type for additional parameter
    } else {
        $queryCondition = "";
    }

    switch ($target) {
        case 'head-parish':
        $query = "
            SELECT 
                h.harambee_group_id, 
                h.harambee_group_name, 
                h.harambee_group_target,
                h.description,
                h.date_created,
                COUNT(hm.member_id) AS members_count
            FROM 
                head_parish_harambee_groups h
            LEFT JOIN 
                head_parish_harambee_group_members hm ON h.harambee_group_id = hm.harambee_group_id
            WHERE 
                h.harambee_id = ? $queryCondition";
        
        // Add condition for h.sup_parish_id if sub_parish_id is provided
        if (!is_null($sub_parish_id)) {
            $query .= " AND h.sub_parish_id = ?";
            $params[] = $sub_parish_id; // Bind the sub_parish_id value
            $paramTypes .= "i"; // Append integer type for the new parameter
        }
    
        $query .= " GROUP BY h.harambee_group_id";
        break;


        case 'sub-parish':
            $query = "
                SELECT 
                    h.harambee_group_id, 
                    h.harambee_group_name, 
                    h.harambee_group_target,
                    h.description,
                    h.date_created,
                    COUNT(hm.member_id) AS members_count
                FROM 
                    sub_parish_harambee_groups h
                LEFT JOIN 
                    sub_parish_harambee_group_members hm ON h.harambee_group_id = hm.harambee_group_id
                WHERE 
                    h.harambee_id = ? $queryCondition
                GROUP BY 
                    h.harambee_group_id";
            break;

        case 'community':
            $query = "
                SELECT 
                    h.harambee_group_id, 
                    h.harambee_group_name, 
                    h.harambee_group_target,
                    h.description,
                    h.date_created,
                    COUNT(hm.member_id) AS members_count
                FROM 
                    community_harambee_groups h
                LEFT JOIN 
                    community_harambee_group_members hm ON h.harambee_group_id = hm.harambee_group_id
                WHERE 
                    h.harambee_id = ? $queryCondition
                GROUP BY 
                    h.harambee_group_id";
            break;

        case 'group':
            $query = "
                SELECT 
                    h.harambee_group_id, 
                    h.harambee_group_name, 
                    h.harambee_group_target,
                    h.description,
                    h.date_created,
                    COUNT(hm.member_id) AS members_count
                FROM 
                    groups_harambee_groups h
                LEFT JOIN 
                    groups_harambee_group_members hm ON h.harambee_group_id = hm.harambee_group_id
                WHERE 
                    h.harambee_id = ? $queryCondition
                GROUP BY 
                    h.harambee_group_id";
            break;

        default:
            echo json_encode(["success" => false, "message" => "Invalid target specified"]);
            exit();
    }

    // Execute the query to fetch groups
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param($paramTypes, ...$params); // Use ... to unpack the array
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch and return results
        $groups = [];
        while ($row = $result->fetch_assoc()) {
            // Format the harambee_group_target amount
            $row['harambee_group_target'] = number_format($row['harambee_group_target'], 0);
            // Ensure members_count is provided, default to 0 if no members
            $row['members_count'] = $row['members_count'] ?? 0;

            // Format the date_created to "31 November 2024"
            if ($row['date_created']) {
                $row['date_created'] = date("d F Y", strtotime($row['date_created']));
            }

            // Now fetch the members for this group
            $groupId = $row['harambee_group_id'];
            $membersQuery = "";

            switch ($target) {
                case 'head-parish':
                    $membersQuery = "
                        SELECT 
                            cm.member_id,
                            CONCAT(cm.first_name, ' ', COALESCE(cm.middle_name, ''), ' ', cm.last_name) AS full_name,
                            cm.phone
                        FROM 
                            head_parish_harambee_group_members hhm
                        LEFT JOIN 
                            church_members cm ON hhm.member_id = cm.member_id
                        WHERE 
                            hhm.harambee_group_id = ?";
                    break;

                case 'sub-parish':
                    $membersQuery = "
                        SELECT 
                            cm.member_id,
                            CONCAT(cm.first_name, ' ', COALESCE(cm.middle_name, ''), ' ', cm.last_name) AS full_name,
                            cm.phone
                        FROM 
                            sub_parish_harambee_group_members hhm
                        LEFT JOIN 
                            church_members cm ON hhm.member_id = cm.member_id
                        WHERE 
                            hhm.harambee_group_id = ?";
                    break;

                case 'community':
                    $membersQuery = "
                        SELECT 
                            cm.member_id,
                            CONCAT(cm.first_name, ' ', COALESCE(cm.middle_name, ''), ' ', cm.last_name) AS full_name,
                            cm.phone
                        FROM 
                            community_harambee_group_members hhm
                        LEFT JOIN 
                            church_members cm ON hhm.member_id = cm.member_id
                        WHERE 
                            hhm.harambee_group_id = ?";
                    break;

                case 'group':
                    $membersQuery = "
                        SELECT 
                            cm.member_id,
                            CONCAT(cm.first_name, ' ', COALESCE(cm.middle_name, ''), ' ', cm.last_name) AS full_name,
                            cm.phone
                        FROM 
                            groups_harambee_group_members hhm
                        LEFT JOIN 
                            church_members cm ON hhm.member_id = cm.member_id
                        WHERE 
                            hhm.harambee_group_id = ?";
                    break;
            }

            // Execute members query
            if ($membersStmt = $conn->prepare($membersQuery)) {
                $membersStmt->bind_param("i", $groupId);
                $membersStmt->execute();
                $membersResult = $membersStmt->get_result();

                // Fetch members
                $members = [];
                while ($memberRow = $membersResult->fetch_assoc()) {
                    $members[] = $memberRow; // Collecting member data
                }

                // Add members to group data
                $row['members'] = $members;
            }

            $groups[] = $row; // Add group data including members
        }

        echo json_encode(["success" => true, "groups" => $groups]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to prepare statement: " . $conn->error]);
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
