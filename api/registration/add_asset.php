<?php
header('Content-Type: application/json');
session_start(); // Start session to access session variables
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Check if the database connection was successful
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Ensure only POST requests are allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

// Check if head parish ID is in session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access. Please log in."]);
    exit();
}
$head_parish_id = $_SESSION['head_parish_id'];

// Validate input data
$asset_name = isset($_POST['asset_name']) ? trim($_POST['asset_name']) : '';
$generates_revenue = isset($_POST['generates_revenue']) ? (int)$_POST['generates_revenue'] : 0;

if (empty($asset_name)) {
    echo json_encode(["success" => false, "message" => "Asset name is required"]);
    exit();
}

// ✅ Check for duplicate asset
$checkSql = "SELECT COUNT(*) FROM head_parish_assets WHERE asset_name = ? AND head_parish_id = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("si", $asset_name, $head_parish_id);
$checkStmt->execute();
$checkStmt->bind_result($count);
$checkStmt->fetch();
$checkStmt->close();

if ($count > 0) {
    echo json_encode(["success" => false, "message" => "Asset already exists"]);
    exit();
}

// ✅ Insert asset into the database
$insertSql = "INSERT INTO head_parish_assets (asset_name, generates_revenue, head_parish_id) VALUES (?, ?, ?)";
$insertStmt = $conn->prepare($insertSql);
$insertStmt->bind_param("sii", $asset_name, $generates_revenue, $head_parish_id);

if ($insertStmt->execute()) {
    echo json_encode(["success" => true, "message" => "Asset added successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to add asset: " . $insertStmt->error]);
}

$insertStmt->close();
$conn->close();
?>
