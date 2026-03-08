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
    $limit = isset($_GET['limit']) ? ($_GET['limit'] == 'all' ? PHP_INT_MAX : intval($_GET['limit'])) : 5;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $limit;
    
    // Search query if present
    $searchQuery = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

    // Fetch the head_parish_id from the session
    if (!isset($_SESSION['head_parish_id'])) {
        echo json_encode(["success" => false, "message" => "Head parish ID is not available in the session"]);
        exit();
    }
    $head_parish_id = (int)$_SESSION['head_parish_id'];

    // Base query to fetch assets
    $sql = "SELECT asset_id, asset_name, generates_revenue 
            FROM head_parish_assets 
            WHERE head_parish_id = ?";

    // Apply search filter if provided
    if (!empty($searchQuery)) {
        $sql .= " AND asset_name LIKE '%$searchQuery%'";
    }

    // Add pagination
    $sql .= " LIMIT ? OFFSET ?";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $head_parish_id, $limit, $offset);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $assets = [];
        while ($row = $result->fetch_assoc()) {
            $assets[] = $row;
        }

        // Get total records for pagination
        $countSql = "SELECT COUNT(*) as total FROM head_parish_assets WHERE head_parish_id = ?";
        if (!empty($searchQuery)) {
            $countSql .= " AND asset_name LIKE '%$searchQuery%'";
        }
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("i", $head_parish_id);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRecords = $countResult->fetch_assoc()['total'];

        $totalPages = ($limit == PHP_INT_MAX) ? 1 : ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $assets,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch assets: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
