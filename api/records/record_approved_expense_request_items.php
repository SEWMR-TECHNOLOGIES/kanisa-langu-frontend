<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
date_default_timezone_set('Africa/Nairobi');

// Check login
if (!isset($_SESSION['head_parish_admin_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized: No admin logged in"]);
    exit();
}

$allowed_roles = ['pastor', 'admin', 'accountant', 'secretary'];
$admin_role = $_SESSION['head_parish_admin_role'];

if (!in_array($admin_role, $allowed_roles)) {
    echo json_encode(["success" => false, "message" => "Unauthorized: You are not allowed to perform this action"]);
    exit();
}

$recorded_by = $_SESSION['head_parish_admin_id'];

// Check connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

// Parse input
$data = json_decode(file_get_contents('php://input'), true);

$request_id = isset($data['request_id']) ? trim($data['request_id']) : '';
$target = isset($data['target']) ? trim($data['target']) : '';
$items = isset($data['items']) ? $data['items'] : [];

$valid_targets = ['head-parish', 'sub-parish', 'community', 'group'];

if (empty($request_id)) {
    echo json_encode(["success" => false, "message" => "Expense request ID is required"]);
    exit();
}

if (!in_array($target, $valid_targets)) {
    echo json_encode(["success" => false, "message" => "Invalid target specified"]);
    exit();
}

if (empty($items) || !is_array($items)) {
    echo json_encode(["success" => false, "message" => "No items provided"]);
    exit();
}

function isValidDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

// Validate each item
foreach ($items as $index => $item) {
    if (!isset($item['item_name']) || trim($item['item_name']) === '') {
        echo json_encode(["success" => false, "message" => "Item name is required for item #" . ($index + 1)]);
        exit();
    }

    if (!isset($item['unit_cost']) || !is_numeric($item['unit_cost']) || $item['unit_cost'] < 0) {
        echo json_encode(["success" => false, "message" => "Invalid unit cost for item #" . ($index + 1)]);
        exit();
    }

    if (!isset($item['quantity']) || !is_numeric($item['quantity']) || $item['quantity'] < 0) {
        echo json_encode(["success" => false, "message" => "Invalid quantity for item #" . ($index + 1)]);
        exit();
    }

    if (isset($item['spent_on']) && $item['spent_on'] !== '' && !isValidDate($item['spent_on'])) {
        echo json_encode(["success" => false, "message" => "Invalid date format for item #" . ($index + 1) . ". Use YYYY-MM-DD"]);
        exit();
    }
}

$tableMap = [
    'head-parish' => 'head_parish_expense_request_items',
    'sub-parish' => 'sub_parish_expense_request_items',
    'community' => 'community_expense_request_items',
    'group' => 'group_expense_request_items',
];

$tableName = $tableMap[$target];
$sql = "INSERT INTO $tableName (request_id, item_name, unit_cost, quantity, measure_id, spent_on, recorded_at, recorded_by) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Failed to prepare SQL statement: " . $conn->error]);
    exit();
}

$now = date('Y-m-d H:i:s');

foreach ($items as $item) {
    $item_name = trim($item['item_name']);
    $unit_cost = floatval($item['unit_cost']);
    $quantity = floatval($item['quantity']);
    $measure_id = isset($item['measure_id']) && is_numeric($item['measure_id']) ? intval($item['measure_id']) : null;
    $spent_on = (!empty($item['spent_on'])) ? $item['spent_on'] : null;

    $stmt->bind_param(
        "ssddissi",
        $request_id,
        $item_name,
        $unit_cost,
        $quantity,
        $measure_id,
        $spent_on,
        $now,
        $recorded_by
    );

    if (!$stmt->execute()) {
        echo json_encode(["success" => false, "message" => "Failed to insert item '{$item_name}': " . $stmt->error]);
        $stmt->close();
        exit();
    }
}

$stmt->close();
echo json_encode(["success" => true, "message" => "All items recorded successfully"]);
$conn->close();
exit();
?>
