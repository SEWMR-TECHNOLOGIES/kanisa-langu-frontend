<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

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

    // Additional filter parameters
    $subParishId = isset($_GET['sub_parish_id']) ? intval($_GET['sub_parish_id']) : null;
    $communityId = isset($_GET['community_id']) ? intval($_GET['community_id']) : null;
    $groupId = isset($_GET['group_id']) ? intval($_GET['group_id']) : null;

    // Fetch the head_parish_id from the session
    if (isset($_SESSION['head_parish_id'])) {
        $head_parish_id = (int)$_SESSION['head_parish_id'];
    } else {
        echo json_encode(["success" => false, "message" => "Head parish ID is not available in the session"]);
        exit();
    }

    // Build the query based on the target
    $sql = '';
    $filters = [];
    
    switch ($target) {
        case 'sub-parish':
            $sql = "SELECT seg.revenue_group_id, seg.revenue_group_name, seg.account_id, sp.sub_parish_name, b.account_name
                    FROM sub_parish_revenue_groups seg
                    JOIN sub_parishes sp ON seg.sub_parish_id = sp.sub_parish_id
                    JOIN head_parish_bank_accounts b ON seg.account_id = b.account_id
                    WHERE seg.head_parish_id = ?";

            if ($subParishId) {
                $filters[] = "seg.sub_parish_id = ?";
            }
            break;

        case 'community':
            $sql = "SELECT ceg.revenue_group_id, ceg.revenue_group_name, ceg.account_id, c.community_name, sp.sub_parish_name, b.account_name
                    FROM community_revenue_groups ceg
                    JOIN communities c ON ceg.community_id = c.community_id
                    JOIN sub_parishes sp ON ceg.sub_parish_id = sp.sub_parish_id
                    JOIN head_parish_bank_accounts b ON ceg.account_id = b.account_id
                    WHERE ceg.head_parish_id = ?";

            if ($subParishId) {
                $filters[] = "ceg.sub_parish_id = ?";
            }
            if ($communityId) {
                $filters[] = "ceg.community_id = ?";
            }
            break;

        case 'group':
            $sql = "SELECT geg.revenue_group_id, geg.revenue_group_name, geg.account_id, g.group_name, b.account_name
                    FROM group_revenue_groups geg
                    JOIN groups g ON geg.group_id = g.group_id
                    JOIN head_parish_bank_accounts b ON geg.account_id = b.account_id
                    WHERE geg.head_parish_id = ?";

            if ($groupId) {
                $filters[] = "geg.group_id = ?";
            }
            break;

        case 'head-parish':
        default:
            $sql = "SELECT heg.revenue_group_id, heg.revenue_group_name, heg.account_id, hp.head_parish_name, b.account_name
                    FROM head_parish_revenue_groups heg
                    JOIN head_parishes hp ON heg.head_parish_id = hp.head_parish_id
                    JOIN head_parish_bank_accounts b ON heg.account_id = b.account_id
                    WHERE heg.head_parish_id = ?";
            break;
    }

    // Add filters and pagination to the query
    if (!empty($searchQuery)) {
        $sql .= " AND (" . implode(" LIKE '%$searchQuery%' OR ", ['revenue_group_name', 'account_name']) . " LIKE '%$searchQuery%')";
    }

    if ($filters) {
        $sql .= " AND " . implode(" AND ", $filters);
    }
    $sql .= " LIMIT ? OFFSET ?";

    // Prepare statement and bind parameters dynamically
    $stmt = $conn->prepare($sql);
    $bindParams = [$head_parish_id];
    if ($subParishId) $bindParams[] = $subParishId;
    if ($communityId) $bindParams[] = $communityId;
    if ($groupId) $bindParams[] = $groupId;
    $bindParams[] = $limit;
    $bindParams[] = $offset;
    
    // Convert bind parameters to their types
    $types = str_repeat('i', count($bindParams));
    $stmt->bind_param($types, ...$bindParams);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $expenseData = [];
        while ($row = $result->fetch_assoc()) {
            $expenseData[] = $row;
        }

        // Get total records for pagination
        $countSql = str_replace("SELECT", "SELECT COUNT(*) as total,", $sql);
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param($types, ...$bindParams);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRecords = $countResult->fetch_assoc()['total'];
        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $expenseData,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch revenue groups data: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
