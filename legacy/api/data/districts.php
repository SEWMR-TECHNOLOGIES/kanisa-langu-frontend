<?php
header('Content-Type: application/json');

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Pagination parameters
    $limit = isset($_GET['limit']) ? ($_GET['limit'] == 'all' ? PHP_INT_MAX : intval($_GET['limit'])) : 10;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $limit;
    
    // Search query if present
    $searchQuery = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

    // Region ID filter if present
    $regionId = isset($_GET['region_id']) ? intval($_GET['region_id']) : null;

    // Base query to fetch districts
    $sql = "SELECT 
                dis.district_id as id, dis.district_name as name, r.region_name 
            FROM districts dis
            LEFT JOIN regions r ON dis.region_id = r.region_id";

    // Apply search filter if provided
    if (!empty($searchQuery)) {
        $sql .= " WHERE dis.district_name LIKE '%$searchQuery%' 
                    OR r.region_name LIKE '%$searchQuery%'";
    }

    // Apply region filter if provided
    if ($regionId !== null) {
        $sql .= (strpos($sql, 'WHERE') === false ? " WHERE " : " AND ") . "dis.region_id = $regionId";
    }

    // Add pagination
    $sql .= " LIMIT $limit OFFSET $offset";
    
    // Execute query
    $result = $conn->query($sql);

    if ($result) {
        $districts = [];
        while ($row = $result->fetch_assoc()) {
            $districts[] = $row;
        }

        // Get total records for pagination
        $countSql = "SELECT COUNT(*) as total FROM districts dis
                     LEFT JOIN regions r ON dis.region_id = r.region_id";
        if (!empty($searchQuery)) {
            $countSql .= " WHERE dis.district_name LIKE '%$searchQuery%' 
                           OR r.region_name LIKE '%$searchQuery%'";
        }
        if ($regionId !== null) {
            $countSql .= (strpos($countSql, 'WHERE') === false ? " WHERE " : " AND ") . "dis.region_id = $regionId";
        }
        $countResult = $conn->query($countSql);
        $totalRecords = $countResult->fetch_assoc()['total'];

        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $districts,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch districts: " . $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
