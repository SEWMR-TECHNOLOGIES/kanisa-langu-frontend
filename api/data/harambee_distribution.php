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

    // Fetch the head_parish_id from the session
    if (isset($_SESSION['head_parish_id'])) {
        $head_parish_id = (int)$_SESSION['head_parish_id'];
    } else {
        echo json_encode(["success" => false, "message" => "Head parish ID is not available in the session"]);
        exit();
    }

    // Build the query based on the target
    switch ($target) {
        case 'sub-parish':
            $sql = "SELECT
                        shd.distribution_id,
                        shd.harambee_id,
                        sp.sub_parish_name,
                        shd.amount,
                        sh.description,
                        sh.amount as target,
                        sh.from_date,
                        sh.to_date,
                        sp.sub_parish_id
                    FROM sub_parish_harambee_distribution shd
                    JOIN sub_parishes sp ON shd.sub_parish_id = sp.sub_parish_id
                    JOIN sub_parish_harambee sh ON shd.harambee_id = sh.harambee_id
                    WHERE shd.head_parish_id = ?";

            if (!empty($searchQuery)) {
                $sql .= " AND (sp.sub_parish_name LIKE '%$searchQuery%' 
                                OR sh.description LIKE '%$searchQuery%')";
            }
            break;

        case 'community':
            $sql = "SELECT
                        chd.distribution_id,
                        chd.harambee_id,
                        c.community_name,
                        sp.sub_parish_name,
                        chd.amount,
                        ch.description,
                        ch.amount as target,
                        ch.from_date,
                        ch.to_date,
                        c.community_id
                    FROM community_harambee_distribution chd
                    JOIN communities c ON chd.community_id = c.community_id
                    JOIN sub_parishes sp ON chd.sub_parish_id = sp.sub_parish_id
                    JOIN community_harambee ch ON chd.harambee_id = ch.harambee_id
                    WHERE chd.head_parish_id = ?";

            if (!empty($searchQuery)) {
                $sql .= " AND (c.community_name LIKE '%$searchQuery%' 
                                OR ch.description LIKE '%$searchQuery%')";
            }
            break;

        case 'group':
            $sql = "SELECT
                        ghd.distribution_id,
                        ghd.harambee_id,
                        g.group_name,
                        ghd.amount,
                        gh.description,
                        gh.amount as target,
                        gh.from_date,
                        gh.to_date,
                        g.group_id
                    FROM group_harambee_distribution ghd
                    JOIN groups g ON ghd.group_id = g.group_id
                    JOIN groups_harambee gh ON ghd.harambee_id = gh.harambee_id
                    WHERE ghd.head_parish_id = ?";

            if (!empty($searchQuery)) {
                $sql .= " AND (g.group_name LIKE '%$searchQuery%' 
                                OR gh.description LIKE '%$searchQuery%')";
            }
            break;

        case 'head-parish':
        default:
            $sql = "SELECT
                        hhd.distribution_id,
                        hhd.harambee_id,
                        hp.head_parish_name,
                        sp.sub_parish_name,
                        hhd.amount,
                        hh.description,
                        hh.amount as target,
                        hh.from_date,
                        hh.to_date,
                        sp.sub_parish_id
                    FROM head_parish_harambee_distribution hhd
                    JOIN head_parishes hp ON hhd.head_parish_id = hp.head_parish_id
                    JOIN sub_parishes sp ON hhd.sub_parish_id = sp.sub_parish_id
                    JOIN head_parish_harambee hh ON hhd.harambee_id = hh.harambee_id
                    WHERE hhd.head_parish_id = ?";

            if (!empty($searchQuery)) {
                $sql .= " AND (hp.head_parish_name LIKE '%$searchQuery%' 
                                OR hh.description LIKE '%$searchQuery%' 
                                OR sp.sub_parish_name LIKE '%$searchQuery%')";
            }
            break;
    }

    // Add pagination
    $sql .= " LIMIT $limit OFFSET $offset";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $head_parish_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $distributionData = [];
        while ($row = $result->fetch_assoc()) {
            // Calculate percentage only if target is not zero
            if (isset($row['target']) && $row['target'] != 0) {
                $percentage = calculatePercentage($row['amount'], $row['target']);
            } else {
                $percentage = 'N/A'; 
            }
            $row['percentage'] = $percentage;
            
            $row['amount'] = number_format((float)$row['amount'], 0);
            // Format the from_date and to_date
            if (!empty($row['from_date'])) {
                $dateFrom = new DateTime($row['from_date']);
                $row['from_date'] = $dateFrom->format('d M Y');
            }
            if (!empty($row['to_date'])) {
                $dateTo = new DateTime($row['to_date']);
                $row['to_date'] = $dateTo->format('d M Y');
            }

            $distributionData[] = $row;
        }

        // Get total records for pagination
        $countSql = str_replace("SELECT", "SELECT COUNT(*) as total,", $sql);
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("i", $head_parish_id);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRecords = $countResult->fetch_assoc()['total'];
        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $distributionData,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch distribution data: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
