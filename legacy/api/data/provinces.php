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

    // Base query to fetch provinces with diocese, region, and district names
    $sql = "SELECT 
                p.province_id, p.province_name, p.province_address, p.province_email, p.province_phone, 
                d.diocese_name, 
                r.region_name, 
                dis.district_name
            FROM provinces p
            LEFT JOIN dioceses d ON p.diocese_id = d.diocese_id
            LEFT JOIN regions r ON p.region_id = r.region_id
            LEFT JOIN districts dis ON p.district_id = dis.district_id";

    // Apply search filter if provided
    if (!empty($searchQuery)) {
        $sql .= " WHERE p.province_name LIKE '%$searchQuery%' 
                    OR d.diocese_name LIKE '%$searchQuery%' 
                    OR r.region_name LIKE '%$searchQuery%' 
                    OR dis.district_name LIKE '%$searchQuery%'";
    }

    // Add pagination
    $sql .= " LIMIT $limit OFFSET $offset";
    
    // Execute query
    $result = $conn->query($sql);

    if ($result) {
        $provinces = [];
        while ($row = $result->fetch_assoc()) {
            $provinces[] = $row;
        }

        // Get total records for pagination
        $countSql = "SELECT COUNT(*) as total FROM provinces p 
                     LEFT JOIN dioceses d ON p.diocese_id = d.diocese_id
                     LEFT JOIN regions r ON p.region_id = r.region_id
                     LEFT JOIN districts dis ON p.district_id = dis.district_id";
        if (!empty($searchQuery)) {
            $countSql .= " WHERE p.province_name LIKE '%$searchQuery%' 
                           OR d.diocese_name LIKE '%$searchQuery%' 
                           OR r.region_name LIKE '%$searchQuery%' 
                           OR dis.district_name LIKE '%$searchQuery%'";
        }
        $countResult = $conn->query($countSql);
        $totalRecords = $countResult->fetch_assoc()['total'];

        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $provinces,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch provinces: " . $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
