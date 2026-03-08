<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
date_default_timezone_set('Africa/Nairobi');

// DB connection check
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Session check
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = (int)$_SESSION['head_parish_id'];

// Only GET allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

// Get params
$limit = isset($_GET['limit']) && $_GET['limit'] !== 'all' ? intval($_GET['limit']) : PHP_INT_MAX;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';

// Base query
$sql = "SELECT exclusion_reason_id, reason, created_at 
        FROM harambee_exclusion_reasons 
        WHERE head_parish_id = ?";

// Add search if needed
if (!empty($searchQuery)) {
    $sql .= " AND reason LIKE CONCAT('%', ?, '%')";
}

// Add pagination
$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";

// Prepare
if (!empty($searchQuery)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isii", $head_parish_id, $searchQuery, $limit, $offset);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $head_parish_id, $limit, $offset);
}

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $reasons = [];

    while ($row = $result->fetch_assoc()) {
        $reasons[] = $row;
    }

    // Count total
    $countSql = "SELECT COUNT(*) AS total FROM harambee_exclusion_reasons WHERE head_parish_id = ?";
    if (!empty($searchQuery)) {
        $countSql .= " AND reason LIKE CONCAT('%', ?, '%')";
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("is", $head_parish_id, $searchQuery);
    } else {
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("i", $head_parish_id);
    }

    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];
    $countStmt->close();

    echo json_encode([
        "success" => true,
        "data" => $reasons,
        "total_pages" => ceil($total / $limit),
        "current_page" => $page
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to load reasons: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
