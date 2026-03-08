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

    // Base query to fetch Sunday services
    $sql = "SELECT 
                s.service_id, 
                s.service_date, 
                s.base_scripture_text, 
                s.large_liturgy_page_number, 
                s.small_liturgy_page_number, 
                s.large_antiphony_page_number, 
                s.small_antiphony_page_number, 
                s.large_praise_page_number, 
                s.small_praise_page_number, 
                c.color_name, 
                c.color_code,
                s.head_parish_id
            FROM sunday_services s
            INNER JOIN church_colors c ON s.service_color_id = c.color_id
            WHERE s.head_parish_id = ?"; // Filter by head_parish_id

    // Apply search filter if provided
    if (!empty($searchQuery)) {
        $sql .= " AND (s.base_scripture_text LIKE '%$searchQuery%' 
                        OR c.color_name LIKE '%$searchQuery%')";
    }

    // Sort by service_date in descending order
    $sql .= " ORDER BY s.service_date DESC";
    
    // Add pagination
    $sql .= " LIMIT $limit OFFSET $offset";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $head_parish_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $services = [];
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }

        // Get total records for pagination
        $countSql = "SELECT COUNT(*) as total FROM sunday_services s WHERE s.head_parish_id = ?";
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("i", $head_parish_id);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRecords = $countResult->fetch_assoc()['total'];

        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $services,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch Sunday services: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
