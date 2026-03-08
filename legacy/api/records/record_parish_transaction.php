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

// Sanitize and validate inputs
$account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : null;
$management_level = isset($_POST['management_level']) ? strtolower(trim($_POST['management_level'])) : null;
$type = isset($_POST['type']) ? strtolower(trim($_POST['type'])) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : null;
$txn_date = isset($_POST['txn_date']) ? $_POST['txn_date'] : date('Y-m-d');

// Extra IDs (may be required depending on level)
$sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : null;
$community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : null;
$group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : null;

// Validation
if (empty($account_id) || empty($management_level)) {
    echo json_encode(["success" => false, "message" => "Management level and account ID are required"]);
    exit();
}

if (!in_array($management_level, ['head-parish','sub-parish','community','group'])) {
    echo json_encode(["success" => false, "message" => "Invalid management level"]);
    exit();
}

if (!in_array($type, ['revenue','expense'])) {
    echo json_encode(["success" => false, "message" => "Transaction type must be 'revenue' or 'expense'"]);
    exit();
}

if (empty($description)) {
    echo json_encode(["success" => false, "message" => "Transaction description is required"]);
    exit();
}

if (!is_numeric($amount) || $amount <= 0) {
    echo json_encode(["success" => false, "message" => "Amount must be a positive number"]);
    exit();
}

// Level-specific validation
switch ($management_level) {
    case 'head-parish':
        $sub_parish_id = $community_id = $group_id = null;
        break;
    case 'sub-parish':
        if (empty($sub_parish_id)) {
            echo json_encode(["success" => false, "message" => "Sub Parish ID is required"]);
            exit();
        }
        $community_id = $group_id = null;
        break;
    case 'community':
        if (empty($sub_parish_id) || empty($community_id)) {
            echo json_encode(["success" => false, "message" => "Sub Parish ID and Community ID are required"]);
            exit();
        }
        $group_id = null;
        break;
    case 'group':
        if (empty($group_id)) {
            echo json_encode(["success" => false, "message" => "Group ID is required"]);
            exit();
        }
        break;
}

// Insert transaction into unified table
$sql = "INSERT INTO transactions 
    (account_id, head_parish_id, sub_parish_id, community_id, group_id,
     management_level, type, description, amount, txn_date, recorded_by, created_at) 
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(["success" => false, "message" => "Prepare failed: ".$conn->error]);
    exit();
}

$created_at = date('Y-m-d H:i:s');
$stmt->bind_param(
    "iiiissssdsss",
    $account_id,
    $head_parish_id,
    $sub_parish_id,
    $community_id,
    $group_id,
    $management_level,
    $type,
    $description,
    $amount,
    $txn_date,
    $admin_id,
    $created_at
);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Transaction recorded successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Insert failed: ".$stmt->error]);
}

$stmt->close();
$conn->close();
