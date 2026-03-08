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

// Build SQL with a single searchable concatenated field for filtering
$sql = "
    SELECT 
        he.*,
        r.reason,
        cm.first_name, cm.middle_name, cm.last_name, cm.envelope_number, cm.phone,
        CASE he.harambee_target
            WHEN 'head-parish' THEN hph.name
            WHEN 'sub-parish' THEN sph.name
            WHEN 'community' THEN ch.name
            WHEN 'group' THEN gh.name
        END AS harambee_name
    FROM harambee_exclusions he
    JOIN harambee_exclusion_reasons r ON he.exclusion_reason_id = r.exclusion_reason_id
    JOIN church_members cm ON he.member_id = cm.member_id
    LEFT JOIN head_parish_harambee hph ON he.harambee_target = 'head-parish' AND he.harambee_id = hph.harambee_id
    LEFT JOIN sub_parish_harambee sph ON he.harambee_target = 'sub-parish' AND he.harambee_id = sph.harambee_id
    LEFT JOIN community_harambee ch ON he.harambee_target = 'community' AND he.harambee_id = ch.harambee_id
    LEFT JOIN groups_harambee gh ON he.harambee_target = 'group' AND he.harambee_id = gh.harambee_id
    WHERE he.head_parish_id = ?
";

// If search is provided, add a single LIKE on concatenated searchable columns
if (!empty($searchQuery)) {
    // Concatenate all searchable columns into one string and search that
    $sql .= " AND CONCAT(
        r.reason, ' ',
        COALESCE(hph.name, ''), ' ',
        COALESCE(sph.name, ''), ' ',
        COALESCE(ch.name, ''), ' ',
        COALESCE(gh.name, ''), ' ',
        cm.first_name, ' ',
        cm.middle_name, ' ',
        cm.last_name, ' ',
        cm.envelope_number
    ) LIKE CONCAT('%', ?, '%')";
}

$sql .= " ORDER BY he.excluded_datetime DESC LIMIT ? OFFSET ?";

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
        "target" => strtoupper(str_replace('-', ' ', $row['harambee_target'])),
        "harambee_name" => $row['harambee_name'],
        "reason" => $row['reason'],
        "excluded_datetime" => date("d/m/Y, g:i:s A", strtotime($row['excluded_datetime'])),
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
    FROM harambee_exclusions he
    JOIN harambee_exclusion_reasons r ON he.exclusion_reason_id = r.exclusion_reason_id
    JOIN church_members cm ON he.member_id = cm.member_id
    LEFT JOIN head_parish_harambee hph ON he.harambee_target = 'head-parish' AND he.harambee_id = hph.harambee_id
    LEFT JOIN sub_parish_harambee sph ON he.harambee_target = 'sub-parish' AND he.harambee_id = sph.harambee_id
    LEFT JOIN community_harambee ch ON he.harambee_target = 'community' AND he.harambee_id = ch.harambee_id
    LEFT JOIN groups_harambee gh ON he.harambee_target = 'group' AND he.harambee_id = gh.harambee_id
    WHERE he.head_parish_id = ?
";

if (!empty($searchQuery)) {
    $countSql .= " AND CONCAT(
        r.reason, ' ',
        COALESCE(hph.name, ''), ' ',
        COALESCE(sph.name, ''), ' ',
        COALESCE(ch.name, ''), ' ',
        COALESCE(gh.name, ''), ' ',
        cm.first_name, ' ',
        cm.middle_name, ' ',
        cm.last_name, ' ',
        cm.envelope_number
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
