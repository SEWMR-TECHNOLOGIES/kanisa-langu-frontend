<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Unable to connect to the database. Please try again later."
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        "success" => false,
        "message" => "Request method not supported. Please use GET to retrieve church events."
    ]);
    exit();
}

// Validate head parish session
if (empty($_SESSION['head_parish_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Access denied. Your session has expired or you lack permissions to view these events."
    ]);
    exit();
}

$head_parish_id = (int)$_SESSION['head_parish_id'];
$limit = isset($_GET['limit']) ? ($_GET['limit'] === 'all' ? PHP_INT_MAX : intval($_GET['limit'])) : 10;
$page = isset($_GET['page']) ? max(intval($_GET['page']), 1) : 1;
$offset = ($page - 1) * $limit;
$searchQuery = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

// Build query
$sql = "SELECT 
            id,
            title,
            description,
            DATE_FORMAT(event_date, '%d-%m-%Y') AS event_date,
            IFNULL(DATE_FORMAT(end_date, '%d-%m-%Y'), '') AS end_date,
            DATE_FORMAT(start_time, '%H:%i') AS start_time,
            DATE_FORMAT(end_time, '%H:%i') AS end_time,
            location,
            target_audience,
            notes,
            is_active
        FROM church_events
        WHERE head_parish_id = ? AND is_active = 1";

if (!empty($searchQuery)) {
    $sql .= " AND (title LIKE '%$searchQuery%' OR description LIKE '%$searchQuery%' OR location LIKE '%$searchQuery%')";
}

$sql .= " ORDER BY event_date DESC LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $head_parish_id, $limit, $offset);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    $countSql = "SELECT COUNT(*) AS total FROM church_events WHERE head_parish_id = ? AND is_active = 1";
    if (!empty($searchQuery)) {
        $countSql .= " AND (title LIKE '%$searchQuery%' OR description LIKE '%$searchQuery%' OR location LIKE '%$searchQuery%')";
    }
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param("i", $head_parish_id);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalRecords = $countResult->fetch_assoc()['total'];
    $totalPages = ($limit === PHP_INT_MAX) ? 1 : ceil($totalRecords / $limit);

    echo json_encode([
        "success" => true,
        "data" => $events,
        "total_pages" => $totalPages,
        "current_page" => $page,
        "total_records" => $totalRecords
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "An error occurred fetching events: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
