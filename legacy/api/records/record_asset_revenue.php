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
$revenue_amount = isset($_POST['revenue_amount']) ? (float)$_POST['revenue_amount'] : 0;
$revenue_date = isset($_POST['revenue_date']) ? trim($_POST['revenue_date']) : date('Y-m-d');
$description = isset($_POST['description']) ? trim($_POST['description']) : null;

if ($asset_id <= 0 || $revenue_amount <= 0) {
    echo json_encode(["success" => false, "message" => "Valid asset and revenue amount are required"]);
    exit();
}

$checkAssetSql = "SELECT asset_id FROM head_parish_assets WHERE asset_id = ? AND head_parish_id = ? AND generates_revenue = 1";
$checkAssetStmt = $conn->prepare($checkAssetSql);
$checkAssetStmt->bind_param("ii", $asset_id, $head_parish_id);
$checkAssetStmt->execute();
$checkAssetStmt->store_result();

if ($checkAssetStmt->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Invalid or non-revenue generating asset"]);
    exit();
}
$checkAssetStmt->close();

$insertSql = "INSERT INTO head_parish_asset_revenues (asset_id, revenue_amount, revenue_date, description, recorded_by, head_parish_id) 
              VALUES (?, ?, ?, ?, ?, ?)";
$insertStmt = $conn->prepare($insertSql);
$insertStmt->bind_param("idssii", $asset_id, $revenue_amount, $revenue_date, $description, $recorded_by, $head_parish_id);

if ($insertStmt->execute()) {
    echo json_encode(["success" => true, "message" => "Asset revenue recorded successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to record asset revenue: " . $insertStmt->error]);
}

$insertStmt->close();
$conn->close();
?>
