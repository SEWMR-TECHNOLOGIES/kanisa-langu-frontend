<?php
header('Content-Type: application/json');

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Pagination parameters
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $limit;

    // Search query if present
    $searchQuery = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

    // Base query to fetch province admins (excluding password)
    $sql = "SELECT 
                a.province_admin_id as admin_id, a.province_admin_fullname as admin_name, 
                a.province_admin_email as admin_email, a.province_admin_phone as admin_phone, 
                a.province_admin_role as role, p.province_name as province_name
            FROM province_admins a
            LEFT JOIN provinces p ON a.province_id = p.province_id";

    // Apply search filter if provided
    if (!empty($searchQuery)) {
        $sql .= " WHERE a.province_admin_fullname LIKE '%$searchQuery%' 
                    OR p.province_name LIKE '%$searchQuery%' 
                    OR a.province_admin_email LIKE '%$searchQuery%' 
                    OR a.province_admin_phone LIKE '%$searchQuery%'";
    }

    // Add pagination
    $sql .= " LIMIT $limit OFFSET $offset";
    
    // Execute query
    $result = $conn->query($sql);

    if ($result) {
        $admins = [];
        while ($row = $result->fetch_assoc()) {
            $admins[] = $row;
        }

        // Get total records for pagination
        $countSql = "SELECT COUNT(*) as total FROM province_admins a
                     LEFT JOIN provinces p ON a.province_id = p.province_id";
        if (!empty($searchQuery)) {
            $countSql .= " WHERE a.province_admin_fullname LIKE '%$searchQuery%' 
                           OR p.province_name LIKE '%$searchQuery%' 
                           OR a.province_admin_email LIKE '%$searchQuery%' 
                           OR a.province_admin_phone LIKE '%$searchQuery%'";
        }
        $countResult = $conn->query($countSql);
        $totalRecords = $countResult->fetch_assoc()['total'];

        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $admins,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch province admins: " . $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
