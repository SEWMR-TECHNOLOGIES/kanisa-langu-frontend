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

    // Base query to fetch church leaders
    $sql = "SELECT l.leader_id as id, 
               CONCAT(CASE WHEN t.name IS NOT NULL THEN t.name ELSE '' END, ' ', l.first_name, ' ', 
                      CASE WHEN l.middle_name IS NOT NULL THEN l.middle_name ELSE '' END, ' ', l.last_name) AS name,
               l.gender, l.type, 
               r.role_name, 
               CASE 
                   WHEN l.appointment_date = '0000-00-00' THEN 'N/A'
                   ELSE DATE_FORMAT(l.appointment_date, '%d %M %Y')  -- Format as day month year
               END AS appointment_date, 
               CASE 
                   WHEN l.end_date = '0000-00-00' THEN 'N/A'
                   ELSE DATE_FORMAT(l.end_date, '%d %M %Y')  -- Format as day month year
               END AS end_date, 
               l.status
        FROM church_leaders l
        LEFT JOIN titles t ON l.title_id = t.id
        LEFT JOIN church_roles r ON l.role_id = r.role_id
        WHERE l.head_parish_id = ?";



    // Apply search filter if provided
    if (!empty($searchQuery)) {
        $sql .= " AND (l.first_name LIKE '%$searchQuery%' 
                        OR l.middle_name LIKE '%$searchQuery%' 
                        OR l.last_name LIKE '%$searchQuery%' 
                        OR l.gender LIKE '%$searchQuery%')";
    }

    // Add pagination
    $sql .= " LIMIT $limit OFFSET $offset";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $head_parish_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $leaders = [];
        while ($row = $result->fetch_assoc()) {
            $leaders[] = $row;
        }

        // Get total records for pagination
        $countSql = "SELECT COUNT(*) as total FROM church_leaders l WHERE l.head_parish_id = ?";
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("i", $head_parish_id);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRecords = $countResult->fetch_assoc()['total'];

        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $leaders,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch leaders: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();

?>
