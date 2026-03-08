<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$requestId = isset($input['request_id']) ? intval($input['request_id']) : 0;
$target    = $input['target'] ?? '';

if (!$requestId || !in_array($target, ['head-parish','sub-parish','community','group'])) {
    echo json_encode(["success" => false, "message" => "Bad parameters"]);
    exit();
}

$tableMap = [
    'head-parish' => 'head_parish_expense_requests',
    'sub-parish'  => 'sub_parish_expense_requests',
    'community'   => 'community_expense_requests',
    'group'       => 'group_expense_requests',
];

$table = $tableMap[$target];

$sql = "UPDATE `$table` 
        SET grouped_request_id = NULL 
        WHERE request_id = ? AND head_parish_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $requestId, $_SESSION['head_parish_id']);
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Item removed"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to remove item"]);
}
$stmt->close();
$conn->close();
?>
