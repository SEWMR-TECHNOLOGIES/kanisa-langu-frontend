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
    $offset = ($page - 1) * $limit;

    $searchQuery = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

    if (isset($_SESSION['head_parish_id'])) {
        $head_parish_id = (int)$_SESSION['head_parish_id'];
    } else {
        echo json_encode(["success" => false, "message" => "Head parish ID is not available in the session"]);
        exit();
    }

    $target = isset($_GET['target']) ? $conn->real_escape_string($_GET['target']) : 'head-parish';
    $sub_parish_id = isset($_GET['sub_parish_id']) ? intval($_GET['sub_parish_id']) : 0;
    $community_id = isset($_GET['community_id']) ? intval($_GET['community_id']) : 0;
    $group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;

    $mapSql = "";
    $mapStmt = null;
    $mapCount = 0;

    // ✅ NEW: If target is main, we skip map count checking entirely
    if ($target !== 'main') {

        if ($target === 'sub-parish' && $sub_parish_id > 0) {
            $mapSql = "SELECT COUNT(*) as total FROM sub_parish_revenue_stream_map 
                       WHERE head_parish_id = ? AND sub_parish_id = ? AND is_active = 1";
            $mapStmt = $conn->prepare($mapSql);
            $mapStmt->bind_param("ii", $head_parish_id, $sub_parish_id);

        } elseif ($target === 'community' && $sub_parish_id > 0 && $community_id > 0) {
            $mapSql = "SELECT COUNT(*) as total FROM community_revenue_stream_map 
                       WHERE head_parish_id = ? AND sub_parish_id = ? AND community_id = ? AND is_active = 1";
            $mapStmt = $conn->prepare($mapSql);
            $mapStmt->bind_param("iii", $head_parish_id, $sub_parish_id, $community_id);

        } elseif ($target === 'groups' && $group_id > 0) {
            $mapSql = "SELECT COUNT(*) as total FROM group_revenue_stream_map 
                       WHERE head_parish_id = ? AND group_id = ? AND is_active = 1";
            $mapStmt = $conn->prepare($mapSql);
            $mapStmt->bind_param("ii", $head_parish_id, $group_id);

        } else {
            $mapSql = "SELECT COUNT(*) as total FROM head_parish_revenue_stream_map 
                       WHERE head_parish_id = ? AND is_active = 1";
            $mapStmt = $conn->prepare($mapSql);
            $mapStmt->bind_param("i", $head_parish_id);
        }

        if ($mapStmt && $mapStmt->execute()) {
            $mapResult = $mapStmt->get_result();
            $mapCount = (int)$mapResult->fetch_assoc()['total'];
            $mapStmt->close();
        }
    }

    // ✅ NEW: If target is main, force direct pull from revenue streams
    if ($target === 'main') {

        $sql = "SELECT
                    r.revenue_stream_id, 
                    r.revenue_stream_name, 
                    a.account_name
                FROM head_parish_revenue_streams r
                LEFT JOIN head_parish_bank_accounts a ON r.account_id = a.account_id
                WHERE r.head_parish_id = ?";

    } else {

        if ($mapCount > 0) {

            if ($target === 'sub-parish' && $sub_parish_id > 0) {
                $sql = "SELECT
                            r.revenue_stream_id, 
                            r.revenue_stream_name, 
                            a.account_name
                        FROM sub_parish_revenue_stream_map m
                        INNER JOIN head_parish_revenue_streams r ON m.revenue_stream_id = r.revenue_stream_id
                        LEFT JOIN head_parish_bank_accounts a ON r.account_id = a.account_id
                        WHERE m.head_parish_id = ? AND m.sub_parish_id = ? AND m.is_active = 1";

            } elseif ($target === 'community' && $sub_parish_id > 0 && $community_id > 0) {
                $sql = "SELECT
                            r.revenue_stream_id, 
                            r.revenue_stream_name, 
                            a.account_name
                        FROM community_revenue_stream_map m
                        INNER JOIN head_parish_revenue_streams r ON m.revenue_stream_id = r.revenue_stream_id
                        LEFT JOIN head_parish_bank_accounts a ON r.account_id = a.account_id
                        WHERE m.head_parish_id = ? AND m.sub_parish_id = ? AND m.community_id = ? AND m.is_active = 1";

            } elseif ($target === 'groups' && $group_id > 0) {
                $sql = "SELECT
                            r.revenue_stream_id, 
                            r.revenue_stream_name, 
                            a.account_name
                        FROM group_revenue_stream_map m
                        INNER JOIN head_parish_revenue_streams r ON m.revenue_stream_id = r.revenue_stream_id
                        LEFT JOIN head_parish_bank_accounts a ON r.account_id = a.account_id
                        WHERE m.head_parish_id = ? AND m.group_id = ? AND m.is_active = 1";

            } else {
                $sql = "SELECT
                            r.revenue_stream_id, 
                            r.revenue_stream_name, 
                            a.account_name
                        FROM head_parish_revenue_stream_map m
                        INNER JOIN head_parish_revenue_streams r ON m.revenue_stream_id = r.revenue_stream_id
                        LEFT JOIN head_parish_bank_accounts a ON r.account_id = a.account_id
                        WHERE m.head_parish_id = ? AND m.is_active = 1";
            }

        } else {

            $sql = "SELECT
                        r.revenue_stream_id, 
                        r.revenue_stream_name, 
                        a.account_name
                    FROM head_parish_revenue_streams r
                    LEFT JOIN head_parish_bank_accounts a ON r.account_id = a.account_id
                    WHERE r.head_parish_id = ?";
        }
    }

    if (!empty($searchQuery)) {
        $sql .= " AND (r.revenue_stream_name LIKE '%$searchQuery%' 
                        OR a.account_name LIKE '%$searchQuery%')";
    }

    $sql .= " LIMIT $limit OFFSET $offset";

    $stmt = $conn->prepare($sql);

    // ✅ NEW: main target binds only head parish
    if ($target === 'main') {
        $stmt->bind_param("i", $head_parish_id);

    } else {

        if ($mapCount > 0) {
            if ($target === 'sub-parish' && $sub_parish_id > 0) {
                $stmt->bind_param("ii", $head_parish_id, $sub_parish_id);
            } elseif ($target === 'community' && $sub_parish_id > 0 && $community_id > 0) {
                $stmt->bind_param("iii", $head_parish_id, $sub_parish_id, $community_id);
            } elseif ($target === 'groups' && $group_id > 0) {
                $stmt->bind_param("ii", $head_parish_id, $group_id);
            } else {
                $stmt->bind_param("i", $head_parish_id);
            }
        } else {
            $stmt->bind_param("i", $head_parish_id);
        }
    }

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $revenueStreams = [];
        while ($row = $result->fetch_assoc()) {
            $revenueStreams[] = $row;
        }

        // ✅ NEW: total count for main should be based on revenue streams table only
        if ($target === 'main') {

            $countSql = "SELECT COUNT(*) as total 
                        FROM head_parish_revenue_streams r
                        LEFT JOIN head_parish_bank_accounts a ON r.account_id = a.account_id
                        WHERE r.head_parish_id = ?";
            if (!empty($searchQuery)) {
                $countSql .= " AND (r.revenue_stream_name LIKE '%$searchQuery%' OR a.account_name LIKE '%$searchQuery%')";
            }
            $countStmt = $conn->prepare($countSql);
            $countStmt->bind_param("i", $head_parish_id);

        } else {

            if ($mapCount > 0) {
                if ($target === 'sub-parish' && $sub_parish_id > 0) {
                    $countSql = "SELECT COUNT(*) as total
                                FROM sub_parish_revenue_stream_map m
                                INNER JOIN head_parish_revenue_streams r ON m.revenue_stream_id = r.revenue_stream_id
                                LEFT JOIN head_parish_bank_accounts a ON r.account_id = a.account_id
                                WHERE m.head_parish_id = ? AND m.sub_parish_id = ? AND m.is_active = 1";
                    if (!empty($searchQuery)) {
                        $countSql .= " AND (r.revenue_stream_name LIKE '%$searchQuery%' OR a.account_name LIKE '%$searchQuery%')";
                    }
                    $countStmt = $conn->prepare($countSql);
                    $countStmt->bind_param("ii", $head_parish_id, $sub_parish_id);

                } elseif ($target === 'community' && $sub_parish_id > 0 && $community_id > 0) {
                    $countSql = "SELECT COUNT(*) as total
                                FROM community_revenue_stream_map m
                                INNER JOIN head_parish_revenue_streams r ON m.revenue_stream_id = r.revenue_stream_id
                                LEFT JOIN head_parish_bank_accounts a ON r.account_id = a.account_id
                                WHERE m.head_parish_id = ? AND m.sub_parish_id = ? AND m.community_id = ? AND m.is_active = 1";
                    if (!empty($searchQuery)) {
                        $countSql .= " AND (r.revenue_stream_name LIKE '%$searchQuery%' OR a.account_name LIKE '%$searchQuery%')";
                    }
                    $countStmt = $conn->prepare($countSql);
                    $countStmt->bind_param("iii", $head_parish_id, $sub_parish_id, $community_id);

                } elseif ($target === 'groups' && $group_id > 0) {
                    $countSql = "SELECT COUNT(*) as total
                                FROM group_revenue_stream_map m
                                INNER JOIN head_parish_revenue_streams r ON m.revenue_stream_id = r.revenue_stream_id
                                LEFT JOIN head_parish_bank_accounts a ON r.account_id = a.account_id
                                WHERE m.head_parish_id = ? AND m.group_id = ? AND m.is_active = 1";
                    if (!empty($searchQuery)) {
                        $countSql .= " AND (r.revenue_stream_name LIKE '%$searchQuery%' OR a.account_name LIKE '%$searchQuery%')";
                    }
                    $countStmt = $conn->prepare($countSql);
                    $countStmt->bind_param("ii", $head_parish_id, $group_id);

                } else {
                    $countSql = "SELECT COUNT(*) as total
                                FROM head_parish_revenue_stream_map m
                                INNER JOIN head_parish_revenue_streams r ON m.revenue_stream_id = r.revenue_stream_id
                                LEFT JOIN head_parish_bank_accounts a ON r.account_id = a.account_id
                                WHERE m.head_parish_id = ? AND m.is_active = 1";
                    if (!empty($searchQuery)) {
                        $countSql .= " AND (r.revenue_stream_name LIKE '%$searchQuery%' OR a.account_name LIKE '%$searchQuery%')";
                    }
                    $countStmt = $conn->prepare($countSql);
                    $countStmt->bind_param("i", $head_parish_id);
                }
            } else {
                $countSql = "SELECT COUNT(*) as total FROM head_parish_revenue_streams r 
                            WHERE r.head_parish_id = ?";
                if (!empty($searchQuery)) {
                    $countSql .= " AND (r.revenue_stream_name LIKE '%$searchQuery%')";
                }
                $countStmt = $conn->prepare($countSql);
                $countStmt->bind_param("i", $head_parish_id);
            }
        }

        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRecords = $countResult->fetch_assoc()['total'];

        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $revenueStreams,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);

        $countStmt->close();

    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch revenue streams: " . $stmt->error]);
    }

    $stmt->close();

} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
