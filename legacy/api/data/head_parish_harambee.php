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

    // Fetch the head_parish_id from the session
    if (isset($_SESSION['head_parish_id'])) {
        $head_parish_id = (int)$_SESSION['head_parish_id'];
    } else {
        echo json_encode(["success" => false, "message" => "Head parish ID is not available in the session"]);
        exit();
    }

    // Get filter parameters
    $sub_parish_id = isset($_GET['sub_parish_id']) ? intval($_GET['sub_parish_id']) : null;
    $community_id = isset($_GET['community_id']) ? intval($_GET['community_id']) : null;
    $group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : null;

    // Build the query based on the target
    switch ($target) {
        case 'sub-parish':
            $sql = "SELECT
                        sh.harambee_id,
                        sh.name,
                        sh.description,
                        sh.from_date,
                        sh.to_date,
                        sh.amount,
                        sh.account_id,
                        sp.sub_parish_name,
                        b.account_name
                    FROM sub_parish_harambee sh
                    JOIN sub_parishes sp ON sh.sub_parish_id = sp.sub_parish_id
                    JOIN head_parish_bank_accounts b ON sh.account_id = b.account_id
                    WHERE sh.head_parish_id = ?";

            // Apply filters if provided
            if ($sub_parish_id) {
                $sql .= " AND sh.sub_parish_id = $sub_parish_id";
            }

            // Apply search filter if provided
            if (!empty($searchQuery)) {
                $sql .= " AND (sh.description LIKE '%$searchQuery%' 
                                OR sh.name LIKE '%$searchQuery%'
                                OR sp.sub_parish_name LIKE '%$searchQuery%' 
                                OR b.account_name LIKE '%$searchQuery%')";
            }
            break;

        case 'community':
            $sql = "SELECT
                        ch.harambee_id,
                        ch.name,
                        ch.description,
                        ch.from_date,
                        ch.to_date,
                        ch.amount,
                        ch.account_id,
                        c.community_name,
                        sp.sub_parish_name,
                        b.account_name
                    FROM community_harambee ch
                    JOIN communities c ON ch.community_id = c.community_id
                    JOIN sub_parishes sp ON ch.sub_parish_id = sp.sub_parish_id
                    JOIN head_parish_bank_accounts b ON ch.account_id = b.account_id
                    WHERE ch.head_parish_id = ?";

            // Apply filters if provided
            if ($community_id) {
                $sql .= " AND ch.community_id = $community_id";
            }

            // Apply search filter if provided
            if (!empty($searchQuery)) {
                $sql .= " AND (ch.description LIKE '%$searchQuery%'
                                OR ch.name LIKE '%$searchQuery%'
                                OR c.community_name LIKE '%$searchQuery%'
                                OR sp.sub_parish_name LIKE '%$searchQuery%' 
                                OR b.account_name LIKE '%$searchQuery%')";
            }
            break;

        case 'group':
        case 'groups':
            $sql = "SELECT
                        gh.harambee_id,
                        gh.name,
                        gh.description,
                        gh.from_date,
                        gh.to_date,
                        gh.amount,
                        gh.account_id,
                        g.group_name,
                        b.account_name
                    FROM groups_harambee gh
                    JOIN groups g ON gh.group_id = g.group_id
                    JOIN head_parish_bank_accounts b ON gh.account_id = b.account_id
                    WHERE gh.head_parish_id = ?";

            // Apply filters if provided
            if ($group_id) {
                $sql .= " AND gh.group_id = $group_id";
            }

            // Apply search filter if provided
            if (!empty($searchQuery)) {
                $sql .= " AND (gh.description LIKE '%$searchQuery%'
                                OR gh.name LIKE '%$searchQuery%' 
                                OR g.group_name LIKE '%$searchQuery%' 
                                OR b.account_name LIKE '%$searchQuery%')";
            }
            break;

        case 'head-parish':
        default:
            $sql = "SELECT
                        hh.harambee_id,
                        hh.name,
                        hh.description,
                        hh.from_date,
                        hh.to_date,
                        hh.amount,
                        hh.account_id,
                        hp.head_parish_name,
                        b.account_name
                    FROM head_parish_harambee hh
                    JOIN head_parishes hp ON hh.head_parish_id = hp.head_parish_id
                    JOIN head_parish_bank_accounts b ON hh.account_id = b.account_id
                    WHERE hh.head_parish_id = ?";

            // Apply search filter if provided
            if (!empty($searchQuery)) {
                $sql .= " AND (hh.description LIKE '%$searchQuery%' 
                                OR hp.name LIKE '%$searchQuery%'
                                OR hp.head_parish_name LIKE '%$searchQuery%' 
                                OR b.account_name LIKE '%$searchQuery%')";
            }
            break;
    }
    
    // Sort by most recent first
    $sql .= " ORDER BY harambee_id DESC";


    // Add pagination
    $sql .= " LIMIT $limit OFFSET $offset";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $head_parish_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $harambeeData = [];
        while ($row = $result->fetch_assoc()) {
            $row['amount'] = number_format((float)$row['amount'], 0);
            
            // Format the dates
            if (!empty($row['from_date'])) {
                $dateFrom = new DateTime($row['from_date']);
                $row['from_date'] = $dateFrom->format('d M Y');
            }
            if (!empty($row['to_date'])) {
                $dateTo = new DateTime($row['to_date']);
                $row['to_date'] = $dateTo->format('d M Y');
            }
    
            $harambeeData[] = $row;
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
            "data" => $harambeeData,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch Harambee data: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
