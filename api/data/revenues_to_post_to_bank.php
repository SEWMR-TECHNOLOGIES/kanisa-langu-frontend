<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

date_default_timezone_set('Africa/Nairobi');

// Ensure admin is logged in
if (!isset($_SESSION['head_parish_admin_id']) || !isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Admin not logged in"]);
    exit();
}

$admin_id = $_SESSION['head_parish_admin_id'];
$head_parish_id = $_SESSION['head_parish_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

$target = isset($_POST['target']) ? strtolower(trim($_POST['target'])) : null;
if (!$target || !in_array($target, ['head_parish','other','sub_parish','community','group'])) {
    echo json_encode(["success" => false, "message" => "Invalid target"]);
    exit();
}

// Map target → table and management level
$tableMap = [
    'head_parish' => ['table' => 'head_parish_revenues', 'level' => 'head-parish'],
    'other' => ['table' => 'other_head_parish_revenues', 'level' => 'head-parish'],
    'sub_parish' => ['table' => 'sub_parish_revenues', 'level' => 'sub-parish'],
    'community' => ['table' => 'community_revenues', 'level' => 'community'],
    'group' => ['table' => 'group_revenues', 'level' => 'group'],
];

$table = $tableMap[$target]['table'];
$management_level = $tableMap[$target]['level'];

// Build SELECT extra fields, JOINs, and GROUP BY depending on target
$selectExtra = '';
$joinExtra = '';
$groupBy = "r.revenue_stream_id, r.revenue_date";

switch ($target) {
    case 'sub_parish':
        $selectExtra = "r.sub_parish_id, sp.sub_parish_name";
        $joinExtra = "LEFT JOIN sub_parishes sp ON r.sub_parish_id = sp.sub_parish_id";
        $groupBy .= ", r.sub_parish_id";
        break;
    case 'community':
        $selectExtra = "r.sub_parish_id, r.community_id, sp.sub_parish_name, c.community_name";
        $joinExtra = "
            LEFT JOIN sub_parishes sp ON r.sub_parish_id = sp.sub_parish_id
            LEFT JOIN communities c ON r.community_id = c.community_id
        ";
        $groupBy .= ", r.sub_parish_id, r.community_id";
        break;
    case 'group':
        $selectExtra = "r.group_id, g.group_name";
        $joinExtra = "LEFT JOIN groups g ON r.group_id = g.group_id";
        $groupBy .= ", r.group_id";
        break;
}

// Build SELECT clause safely
$selectClause = "r.revenue_stream_id, s.revenue_stream_name, s.account_id, r.revenue_date";
if (!empty($selectExtra)) {
    $selectClause .= ", $selectExtra";
}

try {
    $sql = "
        SELECT GROUP_CONCAT(r.revenue_id) AS revenue_ids,
               $selectClause,
               SUM(r.revenue_amount) AS total_amount
        FROM $table r
        JOIN head_parish_revenue_streams s ON r.revenue_stream_id = s.revenue_stream_id
        $joinExtra
        WHERE r.head_parish_id = ? AND r.is_posted_to_bank = FALSE
        GROUP BY $groupBy
        ORDER BY r.revenue_date ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $head_parish_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $item = [
            'revenue_ids' => explode(',', $row['revenue_ids']),
            'account_id' => $row['account_id'],
            'revenue_stream_id' => $row['revenue_stream_id'],
            'revenue_stream_name' => $row['revenue_stream_name'],
            'total_amount' => number_format($row['total_amount'],0),
            'revenue_date' => $row['revenue_date'],
        ];

        // Include extra names only for sub_parish, community, or group
        if ($target === 'sub_parish') {
            $item['sub_parish_id'] = $row['sub_parish_id'];
            $item['sub_parish_name'] = $row['sub_parish_name'];
        }
        if ($target === 'community') {
            $item['sub_parish_id'] = $row['sub_parish_id'];
            $item['sub_parish_name'] = $row['sub_parish_name'];
            $item['community_id'] = $row['community_id'];
            $item['community_name'] = $row['community_name'];
        }
        if ($target === 'group') {
            $item['group_id'] = $row['group_id'];
            $item['group_name'] = $row['group_name'];
        }

        // Log each item for debugging
        // /error_log(json_encode($item) . PHP_EOL, 3, $_SERVER['DOCUMENT_ROOT'] . '/logs/revenue_preview.log');

        $data[] = $item;
    }

    echo json_encode([
        "success" => true,
        "target" => $target,
        "data" => $data,
        "message" => count($data) ? "Fetched revenues successfully" : "No unposted revenues found"
    ]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

$conn->close();
