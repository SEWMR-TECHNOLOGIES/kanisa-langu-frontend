<?php
header('Content-Type: application/json');
session_start(); // Ensure session is started to access session variables

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

    // Fetch the logged-in province ID from the session
    if (isset($_SESSION['province_id'])) {
        $province_id = (int)$_SESSION['province_id'];
    } else {
        echo json_encode(["success" => false, "message" => "Province ID is not available in the session"]);
        exit();
    }

    // Base query to fetch head parish admins for the logged-in province
    $sql = "SELECT 
                hpa.head_parish_admin_id as admin_id, 
                hpa.head_parish_admin_fullname as admin_name, 
                hpa.head_parish_admin_email as admin_email, 
                hpa.head_parish_admin_phone as admin_phone, 
                hpa.head_parish_admin_role as role, 
                hp.head_parish_name as parish_name
            FROM head_parish_admins hpa
            LEFT JOIN head_parishes hp ON hpa.head_parish_id = hp.head_parish_id
            WHERE hp.province_id = ?";  // Use hp.province_id since it's in head_parishes

    // Apply search filter if provided
    if (!empty($searchQuery)) {
        $sql .= " AND (hpa.head_parish_admin_fullname LIKE '%$searchQuery%' 
                    OR hp.head_parish_name LIKE '%$searchQuery%' 
                    OR hpa.head_parish_admin_email LIKE '%$searchQuery%' 
                    OR hpa.head_parish_admin_phone LIKE '%$searchQuery%')";
    }

    // Add pagination
    $sql .= " LIMIT ? OFFSET ?";

    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $province_id, $limit, $offset);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $admins = [];
        while ($row = $result->fetch_assoc()) {
            $admins[] = $row;
        }

        // Get total records for pagination
        $countSql = "SELECT COUNT(*) as total FROM head_parish_admins hpa
                     LEFT JOIN head_parishes hp ON hpa.head_parish_id = hp.head_parish_id
                     WHERE hp.province_id = ?";
        if (!empty($searchQuery)) {
            $countSql .= " AND (hpa.head_parish_admin_fullname LIKE '%$searchQuery%' 
                                OR hpa.head_parish_admin_email LIKE '%$searchQuery%' 
                                OR hpa.head_parish_admin_phone LIKE '%$searchQuery%')";
        }

        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("i", $province_id);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRecords = $countResult->fetch_assoc()['total'];

        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $admins,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch head parish admins: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
