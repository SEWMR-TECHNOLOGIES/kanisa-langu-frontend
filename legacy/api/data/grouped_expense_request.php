<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

$groupedRequestId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$target = isset($_GET['target']) ? $_GET['target'] : '';

if ($groupedRequestId <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid grouped request ID"]);
    exit();
}

if (!in_array($target, ['head-parish', 'sub-parish', 'community', 'group'])) {
    echo json_encode(["success" => false, "message" => "Invalid target"]);
    exit();
}

$headParishId = isset($_SESSION['head_parish_id']) ? intval($_SESSION['head_parish_id']) : 0;
if ($headParishId <= 0) {
    echo json_encode(["success" => false, "message" => "Head parish ID missing from session"]);
    exit();
}

// Build SQL based on target
switch ($target) {
    case 'head-parish':
        $sql = "SELECT
                    hpr.request_id,
                    hpr.request_amount,
                    eg.expense_group_name,
                    en.expense_name
                FROM head_parish_expense_requests hpr
                JOIN head_parish_expense_groups eg ON hpr.expense_group_id = eg.expense_group_id
                JOIN head_parish_expense_names en ON hpr.expense_name_id = en.expense_name_id
                WHERE hpr.grouped_request_id = ? AND hpr.head_parish_id = ?";
        break;

    case 'sub-parish':
        $sql = "SELECT
                    spr.request_id,
                    spr.request_amount,
                    eg.expense_group_name,
                    en.expense_name
                FROM sub_parish_expense_requests spr
                JOIN sub_parish_expense_groups eg ON spr.expense_group_id = eg.expense_group_id
                JOIN sub_parish_expense_names en ON spr.expense_name_id = en.expense_name_id
                WHERE spr.grouped_request_id = ? AND spr.head_parish_id = ?";
        break;

    case 'community':
        $sql = "SELECT
                    cr.request_id,
                    cr.request_amount,
                    eg.expense_group_name,
                    en.expense_name
                FROM community_expense_requests cr
                JOIN community_expense_groups eg ON cr.expense_group_id = eg.expense_group_id
                JOIN community_expense_names en ON cr.expense_name_id = en.expense_name_id
                WHERE cr.grouped_request_id = ? AND cr.head_parish_id = ?";
        break;

    case 'group':
        $sql = "SELECT
                    gr.request_id,
                    gr.request_amount,
                    eg.expense_group_name,
                    en.expense_name
                FROM group_expense_requests gr
                JOIN group_expense_groups eg ON gr.expense_group_id = eg.expense_group_id
                JOIN group_expense_names en ON gr.expense_name_id = en.expense_name_id
                WHERE gr.grouped_request_id = ? AND gr.head_parish_id = ?";
        break;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $groupedRequestId, $headParishId);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = [
        "request_id" => $row['request_id'],
        "expense_name" => $row['expense_name'],
        "request_amount" => number_format($row['request_amount'], 2),
        "expense_group_name" => $row['expense_group_name']
    ];
}

echo json_encode([
    "success" => true,
    "data" => $items
]);

$stmt->close();
$conn->close();
?>
