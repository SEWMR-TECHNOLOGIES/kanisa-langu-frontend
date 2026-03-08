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

    // Base query to fetch praise songs with concatenated name
    $sql = "SELECT 
                song_id as id, 
                song_number, 
                song_name, 
                page_number, 
                created_at, 
                updated_at,
                CONCAT(song_number, ' - ', song_name) AS name
            FROM praise_songs";

    // Apply search filter if provided
    if (!empty($searchQuery)) {
        $sql .= " WHERE song_name LIKE '%$searchQuery%'";
    }

    // Add pagination
    $sql .= " LIMIT $limit OFFSET $offset";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $songs = [];
        while ($row = $result->fetch_assoc()) {
            $songs[] = $row;
        }

        // Get total records for pagination
        $countSql = "SELECT COUNT(*) as total FROM praise_songs";
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRecords = $countResult->fetch_assoc()['total'];

        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $songs,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch praise songs: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
