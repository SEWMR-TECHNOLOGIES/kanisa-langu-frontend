<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
date_default_timezone_set('Africa/Nairobi');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

$head_parish_id = (int)$_SESSION['head_parish_id'];
$limit = isset($_GET['limit']) && $_GET['limit'] !== 'all' ? intval($_GET['limit']) : PHP_INT_MAX;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';

// Base query: only member exclusions
$sql = "
    SELECT 
        cme.exclusion_id,
        cme.excluded_datetime,
        r.reason,
        cm.first_name, cm.middle_name, cm.last_name, cm.envelope_number, cm.phone
    FROM church_member_exclusions cme
    JOIN church_member_exclusion_reasons r ON cme.exclusion_reason_id = r.exclusion_reason_id
    JOIN church_members cm ON cme.member_id = cm.member_id
    WHERE cme.head_parish_id = ?
";

// Add search
if (!empty($searchQuery)) {
    $sql .= " AND CONCAT(
        cm.first_name, ' ',
        cm.middle_name, ' ',
        cm.last_name, ' ',
        cm.envelope_number, ' ',
        cm.phone,
        ' ', r.reason
    ) LIKE CONCAT('%', ?, '%')";
}

$sql .= " ORDER BY cme.excluded_datetime DESC LIMIT ? OFFSET ?";

if (!empty($searchQuery)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isii", $head_parish_id, $searchQuery, $limit, $offset);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $head_parish_id, $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

$exclusions = [];
while ($row = $result->fetch_assoc()) {
    $exclusions[] = [
        "exclusion_id" => $row['exclusion_id'],
        "excluded_datetime" => date("d/m/Y, g:i:s A", strtotime($row['excluded_datetime'])),
        "reason" => $row['reason'],
        "member" => [
            "full_name" => trim($row['first_name'] . " " . $row['middle_name'] . " " . $row['last_name']),
            "envelope_number" => $row['envelope_number'],
            "phone" => $row['phone'],
        ]
    ];
}
$stmt->close();

// Count total
$countSql = "
    SELECT COUNT(*) as total
    FROM church_member_exclusions cme
    JOIN church_member_exclusion_reasons r ON cme.exclusion_reason_id = r.exclusion_reason_id
    JOIN church_members cm ON cme.member_id = cm.member_id
    WHERE cme.head_parish_id = ?";

if (!empty($searchQuery)) {
    $countSql .= " AND CONCAT(
        cm.first_name, ' ',
        cm.middle_name, ' ',
        cm.last_name, ' ',
        cm.envelope_number, ' ',
        cm.phone,
        ' ', r.reason
    ) LIKE CONCAT('%', ?, '%')";
}

if (!empty($searchQuery)) {
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
    "data" => $exclusions,
    "total_pages" => ceil($total / $limit),
    "current_page" => $page
]);

$conn->close();
?>
