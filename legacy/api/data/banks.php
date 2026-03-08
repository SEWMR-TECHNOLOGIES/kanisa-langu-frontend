<?php
header('Content-Type: application/json');

// Database connection
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Check if the database connection is successful
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Pagination parameters
    $limit = isset($_GET['limit']) ? ($_GET['limit'] === 'all' ? PHP_INT_MAX : intval($_GET['limit'])) : 5;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $limit;
    
    // Search query if present
    $searchQuery = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

    // Base query to fetch banks
    $sql = "SELECT bank_id, bank_name FROM banks";

    // Apply search filter if provided
    if (!empty($searchQuery)) {
        $sql .= " WHERE bank_name LIKE '%$searchQuery%'";
    }

    // Add pagination
    $sql .= " LIMIT $limit OFFSET $offset";

    // Execute query
    $result = $conn->query($sql);

    if ($result) {
        $banks = [];
        while ($row = $result->fetch_assoc()) {
            $banks[] = $row;
        }

        // Get total records for pagination
        $countSql = "SELECT COUNT(*) as total FROM banks";
        if (!empty($searchQuery)) {
            $countSql .= " WHERE bank_name LIKE '%$searchQuery%'";
        }
        $countResult = $conn->query($countSql);
        $totalRecords = $countResult->fetch_assoc()['total'];

        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $banks,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch banks: " . $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
