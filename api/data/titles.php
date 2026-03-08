<?php
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $limit = isset($_GET['limit']) ? ($_GET['limit'] == 'all' ? PHP_INT_MAX : intval($_GET['limit'])) : 10;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $limit;

    $searchQuery = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';
    $sql = "SELECT id, name, description FROM titles";

    if (!empty($searchQuery)) {
        $sql .= " WHERE name LIKE '%$searchQuery%'";
    }

    $sql .= " LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);

    if ($result) {
        $titles = [];
        while ($row = $result->fetch_assoc()) {
            $titles[] = $row;
        }

        $countSql = "SELECT COUNT(*) as total FROM titles";
        if (!empty($searchQuery)) {
            $countSql .= " WHERE name LIKE '%$searchQuery%'";
        }
        $countResult = $conn->query($countSql);
        $totalRecords = $countResult->fetch_assoc()['total'];

        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $titles,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch titles: " . $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
