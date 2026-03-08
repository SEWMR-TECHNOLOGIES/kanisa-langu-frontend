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
    $limit = isset($_GET['limit']) ? ($_GET['limit'] === 'all' ? PHP_INT_MAX : intval($_GET['limit'])) : 10;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
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

    // Check if sub_parish_id or community_id is provided in the URL
    $sub_parish_id = isset($_GET['sub_parish_id']) ? intval($_GET['sub_parish_id']) : null;
    $community_id = isset($_GET['community_id']) ? intval($_GET['community_id']) : null;

    // Base query to fetch church members with accounts
    $sql = "SELECT a.account_id, a.member_id, m.first_name, m.middle_name, m.last_name, m.type, 
                   a.phone, m.envelope_number, 
                   DATE_FORMAT(a.created_at, '%d-%m-%Y') AS account_created_at,
                   sp.sub_parish_name, c.community_name
            FROM church_members_accounts a
            JOIN church_members m ON a.member_id = m.member_id
            LEFT JOIN sub_parishes sp ON m.sub_parish_id = sp.sub_parish_id
            LEFT JOIN communities c ON m.community_id = c.community_id
            WHERE m.head_parish_id = ?";

    // Filter by sub_parish_id if provided
    if ($sub_parish_id !== null) {
        $sql .= " AND m.sub_parish_id = $sub_parish_id";
    }

    // Filter by community_id if provided
    if ($community_id !== null) {
        $sql .= " AND m.community_id = $community_id";
    }

    // Apply search filter if provided
    if (!empty($searchQuery)) {
        $sql .= " AND (CONCAT(m.first_name, ' ', m.middle_name, ' ', m.last_name) LIKE '%$searchQuery%' 
                        OR m.first_name LIKE '%$searchQuery%' 
                        OR m.middle_name LIKE '%$searchQuery%' 
                        OR m.last_name LIKE '%$searchQuery%' 
                        OR a.phone LIKE '%$searchQuery%' 
                        OR m.envelope_number LIKE '%$searchQuery%')";
    }

    // Add ordering and pagination
    $sql .= " ORDER BY a.created_at DESC LIMIT $limit OFFSET $offset";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $head_parish_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $members = [];
        while ($row = $result->fetch_assoc()) {
            $members[] = $row;
        }

        // Get total records for pagination
        $countSql = "SELECT COUNT(*) as total FROM church_members_accounts a
                     JOIN church_members m ON a.member_id = m.member_id
                     WHERE m.head_parish_id = ?";
        if ($sub_parish_id !== null) {
            $countSql .= " AND m.sub_parish_id = $sub_parish_id";
        }
        if ($community_id !== null) {
            $countSql .= " AND m.community_id = $community_id";
        }
        if (!empty($searchQuery)) {
            $countSql .= " AND (
                              CONCAT(m.first_name, ' ', m.middle_name, ' ', m.last_name) LIKE '%$searchQuery%' 
                              OR m.first_name LIKE '%$searchQuery%' 
                              OR m.middle_name LIKE '%$searchQuery%' 
                              OR m.last_name LIKE '%$searchQuery%' 
                              OR a.phone LIKE '%$searchQuery%' 
                              OR m.envelope_number LIKE '%$searchQuery%'
                          )";
        }

        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("i", $head_parish_id);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRecords = $countResult->fetch_assoc()['total'];

        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $members,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch members with accounts: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
