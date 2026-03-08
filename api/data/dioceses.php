<?php
header('Content-Type: application/json');

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Pagination parameters
    $limit = isset($_GET['limit']) ? ($_GET['limit'] == 'all' ? PHP_INT_MAX : intval($_GET['limit'])) : 3;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $limit;
    
    // Search query if present
    $searchQuery = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

    // Base query to fetch dioceses with region, district names, phone, and email
    $sql = "SELECT 
                d.diocese_id, d.diocese_name, d.diocese_address, d.diocese_email, d.diocese_phone, 
                r.region_name, di.district_name
            FROM dioceses d
            LEFT JOIN regions r ON d.region_id = r.region_id
            LEFT JOIN districts di ON d.district_id = di.district_id";

    // Apply search filter if provided
    if (!empty($searchQuery)) {
        $sql .= " WHERE d.diocese_name LIKE '%$searchQuery%' 
                    OR d.diocese_address LIKE '%$searchQuery%' 
                    OR r.region_name LIKE '%$searchQuery%' 
                    OR di.district_name LIKE '%$searchQuery%' 
                    OR d.diocese_phone LIKE '%$searchQuery%'
                    OR d.diocese_email LIKE '%$searchQuery%'";
    }

    // Add pagination
    $sql .= " LIMIT $limit OFFSET $offset";
    
    // Execute query
    $result = $conn->query($sql);

    if ($result) {
        $dioceses = [];
        while ($row = $result->fetch_assoc()) {
            $dioceses[] = $row;
        }

        // Get total records for pagination
        $countSql = "SELECT COUNT(*) as total FROM dioceses d
                     LEFT JOIN regions r ON d.region_id = r.region_id
                     LEFT JOIN districts di ON d.district_id = di.district_id";
        if (!empty($searchQuery)) {
            $countSql .= " WHERE d.diocese_name LIKE '%$searchQuery%' 
                           OR d.diocese_address LIKE '%$searchQuery%' 
                           OR r.region_name LIKE '%$searchQuery%' 
                           OR di.district_name LIKE '%$searchQuery%' 
                           OR d.diocese_phone LIKE '%$searchQuery%'
                           OR d.diocese_email LIKE '%$searchQuery%'";
        }
        $countResult = $conn->query($countSql);
        $totalRecords = $countResult->fetch_assoc()['total'];

        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $dioceses,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch dioceses: " . $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
