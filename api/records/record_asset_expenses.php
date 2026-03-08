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
$recorded_by = $_SESSION['head_parish_admin_id'];

$asset_id = isset($_POST['asset_id']) ? (int)$_POST['asset_id'] : 0;
$expense_amount = isset($_POST['expense_amount']) ? (float)$_POST['expense_amount'] : 0;
$expense_date = isset($_POST['expense_date']) ? trim($_POST['expense_date']) : date('Y-m-d');
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

if ($asset_id <= 0 || $expense_amount <= 0) {
    echo json_encode(["success" => false, "message" => "Valid asset and expense amount are required"]);
    exit();
}

if (empty($description)) {
    echo json_encode(["success" => false, "message" => "Description is required"]);
    exit();
}

$checkAssetSql = "SELECT asset_id FROM head_parish_assets WHERE asset_id = ? AND head_parish_id = ?";
$checkAssetStmt = $conn->prepare($checkAssetSql);
$checkAssetStmt->bind_param("ii", $asset_id, $head_parish_id);
$checkAssetStmt->execute();
$checkAssetStmt->store_result();

if ($checkAssetStmt->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Invalid asset"]);
    exit();
}
$checkAssetStmt->close();

$insertSql = "INSERT INTO head_parish_asset_expenses (asset_id, expense_amount, expense_date, description, recorded_by, head_parish_id) 
              VALUES (?, ?, ?, ?, ?, ?)";
$insertStmt = $conn->prepare($insertSql);
$insertStmt->bind_param("idssii", $asset_id, $expense_amount, $expense_date, $description, $recorded_by, $head_parish_id);

if ($insertStmt->execute()) {
    echo json_encode(["success" => true, "message" => "Asset expense recorded successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to record asset expense: " . $insertStmt->error]);
}

$insertStmt->close();
$conn->close();
?>
