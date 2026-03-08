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
    $sql = "SELECT occupation_id as id, occupation_name as name, description FROM occupations";

    if (!empty($searchQuery)) {
        $sql .= " WHERE occupation_name LIKE '%$searchQuery%'";
    }

    $sql .= " LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);

    if ($result) {
        $occupations = [];
        while ($row = $result->fetch_assoc()) {
            $occupations[] = $row;
        }

        $countSql = "SELECT COUNT(*) as total FROM occupations";
        if (!empty($searchQuery)) {
            $countSql .= " WHERE occupation_name LIKE '%$searchQuery%'";
        }
        $countResult = $conn->query($countSql);
        $totalRecords = $countResult->fetch_assoc()['total'];

        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $occupations,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch occupations: " . $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
