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

    // Check if sub_parish_id is provided in the URL
    $sub_parish_id = isset($_GET['sub_parish_id']) ? intval($_GET['sub_parish_id']) : null;

    // Base query to fetch communities with a default description
    $sql = "SELECT c.community_id, c.community_name, IFNULL(c.description, 'No description provided') as description, 
                   c.sub_parish_id, s.sub_parish_name
            FROM communities c
            LEFT JOIN sub_parishes s ON c.sub_parish_id = s.sub_parish_id
            WHERE c.head_parish_id = ?";

    // If a sub_parish_id is provided, filter communities by the specific sub parish
    if ($sub_parish_id !== null) {
        $sql .= " AND c.sub_parish_id = $sub_parish_id";
    }

    // Apply search filter if provided
    if (!empty($searchQuery)) {
        $sql .= " AND (c.community_name LIKE '%$searchQuery%' 
                        OR s.sub_parish_name LIKE '%$searchQuery%')";
    }

    // Add pagination
    $sql .= " LIMIT $limit OFFSET $offset";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $head_parish_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $communities = [];
        while ($row = $result->fetch_assoc()) {
            $communities[] = $row;
        }

        // Get total records for pagination
        $countSql = "SELECT COUNT(*) as total FROM communities c
                     LEFT JOIN sub_parishes s ON c.sub_parish_id = s.sub_parish_id
                     WHERE c.head_parish_id = ?";
        if ($sub_parish_id !== null) {
            $countSql .= " AND c.sub_parish_id = $sub_parish_id";
        }
        if (!empty($searchQuery)) {
            $countSql .= " AND (c.community_name LIKE '%$searchQuery%' 
                                OR s.sub_parish_name LIKE '%$searchQuery%')";
        }
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("i", $head_parish_id);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRecords = $countResult->fetch_assoc()['total'];

        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $communities,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch communities: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
