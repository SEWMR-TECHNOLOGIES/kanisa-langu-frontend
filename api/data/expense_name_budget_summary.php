<?php
header('Content-Type: application/json');
session_start();
date_default_timezone_set('Africa/Nairobi');

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method. Only POST is allowed."]);
    exit();
}

// Required POST data
$expense_name_id = isset($_POST['expense_name_id']) ? intval($_POST['expense_name_id']) : 0;
if (!$expense_name_id) {
    echo json_encode(["success" => false, "message" => "Missing required field: expense_name_id"]);
    exit();
}

$date = $_POST['date'] ?? '';

if (empty($date)) {
    echo json_encode(["success" => false, "message" => "Please select Date"]);
    exit();
}

$dateObject = DateTime::createFromFormat('Y-m-d', $date);
$valid = $dateObject && $dateObject->format('Y-m-d') === $date;

if (!$valid) {
    echo json_encode(["success" => false, "message" => "Invalid date format"]);
    exit();
}

$target = isset($_POST['target']) ? $_POST['target'] : 'head-parish';
$sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : null;
$community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : null;
$group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : null;

// Head parish ID from session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "head_parish_id not found in session"]);
    exit();
}
$head_parish_id = (int)$_SESSION['head_parish_id'];

// Validate based on target
switch ($target) {
    case 'sub-parish':
        if (!$sub_parish_id) {
            echo json_encode(["success" => false, "message" => "sub_parish_id is required for sub-parish target"]);
            exit();
        }
        break;
    case 'community':
        if (!$sub_parish_id || !$community_id) {
            echo json_encode(["success" => false, "message" => "sub_parish_id and community_id are required for community target"]);
            exit();
        }
        break;
    case 'group':
        if (!$group_id) {
            echo json_encode(["success" => false, "message" => "group_id is required for group target"]);
            exit();
        }
        break;
}

try {
    $summary = getBudgetAndExpenseSummaryByDate(
        $conn,
        $date,
        $head_parish_id,
        $expense_name_id,
        $target,
        $sub_parish_id,
        $community_id,
        $group_id
    );

    echo json_encode([
        "success" => true,
        "data" => $summary
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error while processing: " . $e->getMessage()
    ]);
}

$conn->close();
?>
