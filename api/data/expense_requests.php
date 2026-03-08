<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Pagination parameters
    $limit = isset($_GET['limit']) ? ($_GET['limit'] == 'all' ? PHP_INT_MAX : intval($_GET['limit'])) : 5;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $target = isset($_GET['target']) ? $_GET['target'] : 'head-parish';
    $offset = ($page - 1) * $limit;

    // Search query if present
    $searchQuery = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';
    $normalizedQuery = trim(strtolower($searchQuery));
    $isStatusFilter = in_array($normalizedQuery, ['approved', 'pending']);

    // Fetch the head_parish_id from the session
    if (isset($_SESSION['head_parish_id'])) {
        $head_parish_id = (int)$_SESSION['head_parish_id'];
    } else {
        echo json_encode(["success" => false, "message" => "Head parish ID is not available in the session"]);
        exit();
    }
    
    $sessionRole = isset($_SESSION['head_parish_admin_role']) ? $_SESSION['head_parish_admin_role'] : null;
    $targetRole = ($sessionRole === 'admin') ? 'chairperson' : $sessionRole;

    // Build the base query and count query based on the target
    switch ($target) {
        case 'sub-parish':
            $baseSql = "FROM sub_parish_expense_requests spr
                        JOIN sub_parishes sp ON spr.sub_parish_id = sp.sub_parish_id
                        JOIN sub_parish_expense_groups eg ON spr.expense_group_id = eg.expense_group_id
                        JOIN sub_parish_expense_names en ON spr.expense_name_id = en.expense_name_id
                        WHERE spr.head_parish_id = ?";

            if (!empty($searchQuery)) {
                if ($isStatusFilter) {
                    $statusValue = ucfirst($normalizedQuery);
                    $baseSql .= " AND spr.request_status = '$statusValue'";
                } else {
                    $baseSql .= " AND (sp.sub_parish_name LIKE '%$searchQuery%' 
                                    OR eg.expense_group_name LIKE '%$searchQuery%' 
                                    OR en.expense_name LIKE '%$searchQuery%')";
                }
            }

            $sql = "SELECT
                        spr.request_id,
                        spr.request_amount,
                        spr.request_datetime,
                        spr.request_description,
                        spr.request_status,
                        spr.pastor_seen,
                        spr.pastor_approval,
                        spr.chairperson_seen,
                        spr.chairperson_approval,
                        spr.pastor_rejection_remarks, 
                        spr.chairperson_rejection_remarks,
                        spr.chairperson_approval_datetime,
                        spr.pastor_approval_datetime,
                        sp.sub_parish_name,
                        eg.expense_group_name,
                        en.expense_name
                    $baseSql ORDER BY spr.request_datetime DESC
                    LIMIT $limit OFFSET $offset";

            $countSql = "SELECT COUNT(*) as total $baseSql";
            break;

        case 'community':
            $baseSql = "FROM community_expense_requests cr
                        JOIN communities c ON cr.community_id = c.community_id
                        JOIN community_expense_groups eg ON cr.expense_group_id = eg.expense_group_id
                        JOIN community_expense_names en ON cr.expense_name_id = en.expense_name_id
                        WHERE cr.head_parish_id = ?";

            if (!empty($searchQuery)) {
                if ($isStatusFilter) {
                    $statusValue = ucfirst($normalizedQuery);
                    $baseSql .= " AND cr.request_status = '$statusValue'";
                } else {
                    $baseSql .= " AND (c.community_name LIKE '%$searchQuery%' 
                                    OR eg.expense_group_name LIKE '%$searchQuery%' 
                                    OR en.expense_name LIKE '%$searchQuery%')";
                }
            }

            $sql = "SELECT
                        cr.request_id,
                        cr.request_amount,
                        cr.request_datetime,
                        cr.request_description,
                        cr.request_status,
                        cr.pastor_seen,
                        cr.pastor_approval,
                        cr.chairperson_seen,
                        cr.chairperson_approval,
                        cr.pastor_rejection_remarks, 
                        cr.chairperson_rejection_remarks,
                        cr.chairperson_approval_datetime,
                        cr.pastor_approval_datetime,
                        c.community_name,
                        eg.expense_group_name,
                        en.expense_name
                    $baseSql ORDER BY cr.request_datetime DESC
                    LIMIT $limit OFFSET $offset";

            $countSql = "SELECT COUNT(*) as total $baseSql";
            break;

        case 'group':
            $baseSql = "FROM group_expense_requests gr
                        JOIN groups g ON gr.group_id = g.group_id
                        JOIN group_expense_groups eg ON gr.expense_group_id = eg.expense_group_id
                        JOIN group_expense_names en ON gr.expense_name_id = en.expense_name_id
                        WHERE gr.head_parish_id = ?";

            if (!empty($searchQuery)) {
                if ($isStatusFilter) {
                    $statusValue = ucfirst($normalizedQuery);
                    $baseSql .= " AND gr.request_status = '$statusValue'";
                } else {
                    $baseSql .= " AND (g.group_name LIKE '%$searchQuery%' 
                                    OR eg.expense_group_name LIKE '%$searchQuery%' 
                                    OR en.expense_name LIKE '%$searchQuery%')";
                }
            }

            $sql = "SELECT
                        gr.request_id,
                        gr.request_amount,
                        gr.request_datetime,
                        gr.request_description,
                        gr.request_status,
                        gr.pastor_seen,
                        gr.pastor_approval,
                        gr.chairperson_seen,
                        gr.chairperson_approval,
                        gr.pastor_rejection_remarks, 
                        gr.chairperson_rejection_remarks, 
                        gr.chairperson_approval_datetime,
                        gr.pastor_approval_datetime,
                        g.group_name,
                        eg.expense_group_name,
                        en.expense_name
                    $baseSql ORDER BY gr.request_datetime DESC
                    LIMIT $limit OFFSET $offset";

            $countSql = "SELECT COUNT(*) as total $baseSql";
            break;

        case 'head-parish':
        default:
            $baseSql = "FROM head_parish_expense_requests hpr
                        JOIN head_parishes hp ON hpr.head_parish_id = hp.head_parish_id
                        JOIN head_parish_expense_groups eg ON hpr.expense_group_id = eg.expense_group_id
                        JOIN head_parish_expense_names en ON hpr.expense_name_id = en.expense_name_id
                        WHERE hpr.head_parish_id = ?";

            if (!empty($searchQuery)) {
                if ($isStatusFilter) {
                    $statusValue = ucfirst($normalizedQuery);
                    $baseSql .= " AND hpr.request_status = '$statusValue'";
                } else {
                    $baseSql .= " AND (hp.head_parish_name LIKE '%$searchQuery%' 
                                    OR eg.expense_group_name LIKE '%$searchQuery%' 
                                    OR en.expense_name LIKE '%$searchQuery%')";
                }
            }

            $sql = "SELECT
                        hpr.request_id,
                        hpr.request_amount,
                        hpr.request_datetime,
                        hpr.request_description,
                        hpr.request_status,
                        hpr.pastor_seen,
                        hpr.pastor_approval,
                        hpr.chairperson_seen,
                        hpr.chairperson_approval,
                        hpr.pastor_rejection_remarks, 
                        hpr.chairperson_rejection_remarks,
                        hpr.chairperson_approval_datetime,
                        hpr.pastor_approval_datetime,
                        hp.head_parish_name,
                        eg.expense_group_name,
                        en.expense_name
                    $baseSql ORDER BY hpr.request_datetime DESC
                    LIMIT $limit OFFSET $offset";

            $countSql = "SELECT COUNT(*) as total $baseSql";
            break;
    }

    // Prepare and execute the main query
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
        exit();
    }
    $stmt->bind_param("i", $head_parish_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $expenseData = [];
        while ($row = $result->fetch_assoc()) {
            // Format date
            if (!empty($row['request_datetime'])) {
                $dateTime = new DateTime($row['request_datetime']);
                $row['request_datetime'] = $dateTime->format('d M Y, H:i');
                $row['request_amount'] = number_format($row['request_amount'], 0);
            }
            
            // Format chairperson approval datetime
            if (!empty($row['chairperson_approval_datetime'])) {
                $dateTime = new DateTime($row['chairperson_approval_datetime']);
                $row['chairperson_approval_datetime'] = $dateTime->format('d M Y, H:i');
            }

            // Format pastor approval datetime
            if (!empty($row['pastor_approval_datetime'])) {
                $dateTime = new DateTime($row['pastor_approval_datetime']);
                $row['pastor_approval_datetime'] = $dateTime->format('d M Y, H:i');
            }
            
            $expenseData[] = $row;
        }

        // Prepare and execute the count query (without limit/offset)
        $countStmt = $conn->prepare($countSql);
        if (!$countStmt) {
            echo json_encode(["success" => false, "message" => "Count prepare failed: " . $conn->error]);
            exit();
        }
        $countStmt->bind_param("i", $head_parish_id);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRecords = $countResult->fetch_assoc()['total'];
        $totalPages = $limit === 0 ? 1 : ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $expenseData,
            "total_pages" => $totalPages,
            "current_page" => $page,
            "target_role" => $targetRole
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch expense request data: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
