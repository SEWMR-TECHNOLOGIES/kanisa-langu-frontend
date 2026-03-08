<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Ensure session contains head_parish_id
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

// Check DB connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

// Sanitize inputs
$account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : null;
$target = isset($_POST['target']) ? $_POST['target'] : null;
$closing_balance_date = isset($_POST['closing_balance_date']) ? $conn->real_escape_string($_POST['closing_balance_date']) : '';
$closing_balance = isset($_POST['closing_balance']) ? floatval($_POST['closing_balance']) : null;

// Extra IDs
$sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : null;
$community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : null;
$group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : null;

// Validate
if (empty($account_id) || empty($target)) {
    echo json_encode(["success" => false, "message" => "Target and account ID are required"]);
    exit();
}
if (empty($closing_balance_date)) {
    echo json_encode(["success" => false, "message" => "Closing balance date is required"]);
    exit();
}
if (!is_numeric($closing_balance) || $closing_balance < 0) {
    echo json_encode(["success" => false, "message" => "Closing balance must be a positive number"]);
    exit();
}

// Check date is last day of the month
$expected_date = date('Y-m-t', strtotime($closing_balance_date));
if ($closing_balance_date !== $expected_date) {
    echo json_encode(["success" => false, "message" => "Closing Balance date must be the last day of the month"]);
    exit();
}

// Switch logic based on target
switch ($target) {
    case 'head-parish':
        $table = "head_parish_bank_account_closing_balances";
        $where_clause = "account_id = ? AND head_parish_id = ? AND balance_date = ?";
        $check_types = "iis";
        $check_params = [$account_id, $head_parish_id, $closing_balance_date];
        $update_sql = "UPDATE $table SET closing_balance = ? WHERE $where_clause";
        $insert_sql = "INSERT INTO $table (account_id, head_parish_id, balance_date, closing_balance) VALUES (?, ?, ?, ?)";
        $insert_types = "iisd";
        $insert_params = [$account_id, $head_parish_id, $closing_balance_date, $closing_balance];
        break;

    case 'sub-parish':
        if (empty($sub_parish_id)) {
            echo json_encode(["success" => false, "message" => "Sub Parish ID is required"]);
            exit();
        }
        $table = "sub_parish_bank_account_closing_balances";
        $where_clause = "account_id = ? AND sub_parish_id = ? AND head_parish_id = ? AND balance_date = ?";
        $check_types = "iiis";
        $check_params = [$account_id, $sub_parish_id, $head_parish_id, $closing_balance_date];
        $update_sql = "UPDATE $table SET closing_balance = ? WHERE $where_clause";
        $insert_sql = "INSERT INTO $table (account_id, sub_parish_id, head_parish_id, balance_date, closing_balance) VALUES (?, ?, ?, ?, ?)";
        $insert_types = "iiisd";
        $insert_params = [$account_id, $sub_parish_id, $head_parish_id, $closing_balance_date, $closing_balance];
        break;

    case 'community':
        if (empty($sub_parish_id) || empty($community_id)) {
            echo json_encode(["success" => false, "message" => "Sub Parish ID and Community ID are required"]);
            exit();
        }
        $table = "community_bank_account_closing_balances";
        $where_clause = "account_id = ? AND sub_parish_id = ? AND community_id = ? AND head_parish_id = ? AND balance_date = ?";
        $check_types = "iiiis";
        $check_params = [$account_id, $sub_parish_id, $community_id, $head_parish_id, $closing_balance_date];
        $update_sql = "UPDATE $table SET closing_balance = ? WHERE $where_clause";
        $insert_sql = "INSERT INTO $table (account_id, sub_parish_id, community_id, head_parish_id, balance_date, closing_balance) VALUES (?, ?, ?, ?, ?, ?)";
        $insert_types = "iiiisd";
        $insert_params = [$account_id, $sub_parish_id, $community_id, $head_parish_id, $closing_balance_date, $closing_balance];
        break;

    case 'group':
        if (empty($group_id)) {
            echo json_encode(["success" => false, "message" => "Group ID is required"]);
            exit();
        }
        $table = "groups_bank_account_closing_balances";
        $where_clause = "account_id = ? AND group_id = ? AND head_parish_id = ? AND balance_date = ?";
        $check_types = "iiis";
        $check_params = [$account_id, $group_id, $head_parish_id, $closing_balance_date];
        $update_sql = "UPDATE $table SET closing_balance = ? WHERE $where_clause";
        $insert_sql = "INSERT INTO $table (account_id, group_id, head_parish_id, balance_date, closing_balance) VALUES (?, ?, ?, ?, ?)";
        $insert_types = "iiisd";
        $insert_params = [$account_id, $group_id, $head_parish_id, $closing_balance_date, $closing_balance];
        break;

    default:
        echo json_encode(["success" => false, "message" => "Invalid target type"]);
        exit();
}

// Check if record exists
$check_query = "SELECT balance_id FROM $table WHERE $where_clause";
$stmt = $conn->prepare($check_query);
$stmt->bind_param($check_types, ...$check_params);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("d" . $check_types, $closing_balance, ...$check_params);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Closing balance updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Update failed: " . $stmt->error]);
    }
} else {
    // Insert
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param($insert_types, ...$insert_params);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Closing balance recorded successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Insert failed: " . $stmt->error]);
    }
}

$stmt->close();
$conn->close();
