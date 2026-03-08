<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

if (!isset($_SESSION['head_parish_id'], $_SESSION['head_parish_admin_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access. Please log in."]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

if (!isset($_POST['description']) || empty(trim($_POST['description']))) {
    echo json_encode(["success" => false, "message" => "Description is required"]);
    exit();
}
$description = $conn->real_escape_string(trim($_POST['description']));

if (!isset($_POST['amount']) || !is_numeric($_POST['amount']) || (float)$_POST['amount'] <= 0) {
    echo json_encode(["success" => false, "message" => "Valid amount is required"]);
    exit();
}
$amount = (float)$_POST['amount'];

if (!isset($_POST['date_debited']) || empty(trim($_POST['date_debited']))) {
    echo json_encode(["success" => false, "message" => "Date debited is required"]);
    exit();
}
$date_debited = $conn->real_escape_string(trim($_POST['date_debited']));

if (!isset($_POST['return_before_date']) || empty(trim($_POST['return_before_date']))) {
    echo json_encode(["success" => false, "message" => "Return before date is required"]);
    exit();
}
$return_before_date = $conn->real_escape_string(trim($_POST['return_before_date']));

if (!isset($_POST['purpose']) || empty(trim($_POST['purpose']))) {
    echo json_encode(["success" => false, "message" => "Purpose is required"]);
    exit();
}
$purpose = $conn->real_escape_string(trim($_POST['purpose']));

$insertSql = "INSERT INTO head_parish_debits (head_parish_id, description, amount, date_debited, return_before_date, purpose) 
              VALUES (?, ?, ?, ?, ?, ?)";

$insertStmt = $conn->prepare($insertSql);
if (!$insertStmt) {
    echo json_encode(["success" => false, "message" => "Failed to prepare statement: " . $conn->error]);
    exit();
}

$insertStmt->bind_param("isdsss", $head_parish_id, $description, $amount, $date_debited, $return_before_date, $purpose);

if ($insertStmt->execute()) {
    echo json_encode(["success" => true, "message" => "Debit recorded successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to record debit: " . $insertStmt->error]);
}

$insertStmt->close();
$conn->close();
?>
