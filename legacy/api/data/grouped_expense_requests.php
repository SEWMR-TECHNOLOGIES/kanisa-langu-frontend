<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $limit = isset($_GET['limit']) ? ($_GET['limit'] == 'all' ? PHP_INT_MAX : intval($_GET['limit'])) : 5;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $target = isset($_GET['target']) ? $_GET['target'] : 'head-parish';
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

    if (!isset($_SESSION['head_parish_id'])) {
        echo json_encode(["success" => false, "message" => "Head parish ID not found in session"]);
        exit();
    }

    $head_parish_id = (int)$_SESSION['head_parish_id'];
switch ($target) {
    case 'sub-parish':
        $baseSql = "FROM sub_parish_grouped_expense_requests g
                    JOIN sub_parishes sp ON g.sub_parish_id = sp.sub_parish_id
                    LEFT JOIN head_parish_bank_accounts ba ON g.account_id = ba.account_id
                    WHERE g.head_parish_id = ?";

        if (!empty($search)) {
            $baseSql .= " AND g.description LIKE '%$search%'";
        }

        $sql = "SELECT g.grouped_request_id, g.description, g.submission_datetime,
                       sp.sub_parish_name AS location_name, ba.account_name
                $baseSql ORDER BY g.submission_datetime DESC
                LIMIT $limit OFFSET $offset";

        $countSql = "SELECT COUNT(DISTINCT g.grouped_request_id) as total $baseSql";
        break;

    case 'community':
        $baseSql = "FROM community_grouped_expense_requests g
                    JOIN communities c ON g.community_id = c.community_id
                    LEFT JOIN head_parish_bank_accounts ba ON g.account_id = ba.account_id
                    WHERE g.head_parish_id = ?";

        if (!empty($search)) {
            $baseSql .= " AND g.description LIKE '%$search%'";
        }

        $sql = "SELECT g.grouped_request_id, g.description, g.submission_datetime,
                       c.community_name AS location_name, ba.account_name
                $baseSql ORDER BY g.submission_datetime DESC
                LIMIT $limit OFFSET $offset";

        $countSql = "SELECT COUNT(DISTINCT g.grouped_request_id) as total $baseSql";
        break;

    case 'group':
        $baseSql = "FROM group_grouped_expense_requests g
                    JOIN groups gr ON g.group_id = gr.group_id
                    LEFT JOIN head_parish_bank_accounts ba ON g.account_id = ba.account_id
                    WHERE g.head_parish_id = ?";

        if (!empty($search)) {
            $baseSql .= " AND g.description LIKE '%$search%'";
        }

        $sql = "SELECT g.grouped_request_id, g.description, g.submission_datetime,
                       gr.group_name AS location_name, ba.account_name
                $baseSql ORDER BY g.submission_datetime DESC
                LIMIT $limit OFFSET $offset";

        $countSql = "SELECT COUNT(DISTINCT g.grouped_request_id) as total $baseSql";
        break;

    case 'head-parish':
    default:
        $baseSql = "FROM head_parish_grouped_expense_requests g
                    JOIN head_parishes hp ON g.head_parish_id = hp.head_parish_id
                    LEFT JOIN head_parish_bank_accounts ba ON g.account_id = ba.account_id
                    WHERE g.head_parish_id = ?";

        if (!empty($search)) {
            $baseSql .= " AND g.description LIKE '%$search%'";
        }

        $sql = "SELECT g.grouped_request_id, g.description, g.submission_datetime,
                       hp.head_parish_name AS location_name, ba.account_name
                $baseSql ORDER BY g.submission_datetime DESC
                LIMIT $limit OFFSET $offset";

        $countSql = "SELECT COUNT(DISTINCT g.grouped_request_id) as total $baseSql";
        break;
}


    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("i", $head_parish_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $data = [];

        while ($row = $result->fetch_assoc()) {
            if (!empty($row['submission_datetime'])) {
                $dt = new DateTime($row['submission_datetime']);
                $row['submission_datetime'] = $dt->format('d M Y, H:i');
            }
            $data[] = $row;
        }

        // Count
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("i", $head_parish_id);
        $countStmt->execute();
        $total = $countStmt->get_result()->fetch_assoc()['total'];
        $totalPages = $limit === 0 ? 1 : ceil($total / $limit);

        echo json_encode([
            "success" => true,
            "data" => $data,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Query failed: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
