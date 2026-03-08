<?php
header('Content-Type: application/json');

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Pagination parameters
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 3;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $limit;
    
    // Search query if present
    $searchQuery = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

    // Base query to fetch head parishes with related diocese, province, region, and district names
    $sql = "SELECT 
                hp.head_parish_id, hp.head_parish_name, hp.head_parish_address, hp.head_parish_email, hp.head_parish_phone,
                d.diocese_name, p.province_name, r.region_name, ds.district_name
            FROM head_parishes hp
            LEFT JOIN dioceses d ON hp.diocese_id = d.diocese_id
            LEFT JOIN provinces p ON hp.province_id = p.province_id
            LEFT JOIN regions r ON hp.region_id = r.region_id
            LEFT JOIN districts ds ON hp.district_id = ds.district_id";

    // Apply search filter if provided
    if (!empty($searchQuery)) {
        $sql .= " WHERE hp.head_parish_name LIKE '%$searchQuery%' 
                  OR d.diocese_name LIKE '%$searchQuery%' 
                  OR p.province_name LIKE '%$searchQuery%' 
                  OR r.region_name LIKE '%$searchQuery%' 
                  OR ds.district_name LIKE '%$searchQuery%'";
    }

    // Add pagination
    $sql .= " LIMIT $limit OFFSET $offset";
    
    // Execute query
    $result = $conn->query($sql);

    if ($result) {
        $headParishes = [];
        while ($row = $result->fetch_assoc()) {
            $headParishes[] = $row;
        }

        // Get total records for pagination
        $countSql = "SELECT COUNT(*) as total FROM head_parishes hp
                     LEFT JOIN dioceses d ON hp.diocese_id = d.diocese_id
                     LEFT JOIN provinces p ON hp.province_id = p.province_id
                     LEFT JOIN regions r ON hp.region_id = r.region_id
                     LEFT JOIN districts ds ON hp.district_id = ds.district_id";
        if (!empty($searchQuery)) {
            $countSql .= " WHERE hp.head_parish_name LIKE '%$searchQuery%' 
                           OR d.diocese_name LIKE '%$searchQuery%' 
                           OR p.province_name LIKE '%$searchQuery%' 
                           OR r.region_name LIKE '%$searchQuery%' 
                           OR ds.district_name LIKE '%$searchQuery%'";
        }
        $countResult = $conn->query($countSql);
        $totalRecords = $countResult->fetch_assoc()['total'];

        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $headParishes,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch head parishes: " . $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
