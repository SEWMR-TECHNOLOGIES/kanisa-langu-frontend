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
$asset_id = isset($_POST['asset_id']) ? (int)$_POST['asset_id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : null;
$status_date = isset($_POST['status_date']) ? trim($_POST['status_date']) : date('Y-m-d'); // Default to today

// Check required fields
if (empty($asset_id) || empty($status)) {
    echo json_encode(["success" => false, "message" => "Asset and status are required"]);
    exit();
}

// Ensure valid status
$valid_statuses = ['Nzuri', 'Sio Nzuri'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(["success" => false, "message" => "Invalid status value"]);
    exit();
}

// Ensure asset exists and belongs to the logged-in head parish
$checkAssetSql = "SELECT asset_id FROM head_parish_assets WHERE asset_id = ? AND head_parish_id = ?";
$checkAssetStmt = $conn->prepare($checkAssetSql);
$checkAssetStmt->bind_param("ii", $asset_id, $head_parish_id);
$checkAssetStmt->execute();
$checkAssetStmt->store_result();

if ($checkAssetStmt->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Asset not found or unauthorized"]);
    exit();
}
$checkAssetStmt->close();

// ✅ Check if status already exists for the same asset and date
$checkStatusSql = "SELECT status_id FROM head_parish_asset_status WHERE asset_id = ? AND status_date = ? AND head_parish_id = ?";
$checkStatusStmt = $conn->prepare($checkStatusSql);
$checkStatusStmt->bind_param("isi", $asset_id, $status_date, $head_parish_id);
$checkStatusStmt->execute();
$checkStatusStmt->store_result();

if ($checkStatusStmt->num_rows > 0) {
    // ✅ Update existing status
    $checkStatusStmt->bind_result($status_id);
    $checkStatusStmt->fetch();
    $checkStatusStmt->close();

    $updateSql = "UPDATE head_parish_asset_status SET status = ?, description = ? WHERE status_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ssi", $status, $description, $status_id);

    if ($updateStmt->execute()) {
        echo json_encode(["success" => true, "message" => "Asset status updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update asset status: " . $updateStmt->error]);
    }

    $updateStmt->close();
} else {
    // ✅ Insert new status if no existing record for the same date
    $checkStatusStmt->close();
    $insertSql = "INSERT INTO head_parish_asset_status (asset_id, status, description, status_date, head_parish_id) 
                  VALUES (?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("isssi", $asset_id, $status, $description, $status_date, $head_parish_id);

    if ($insertStmt->execute()) {
        echo json_encode(["success" => true, "message" => "Asset status recorded successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to record asset status: " . $insertStmt->error]);
    }

    $insertStmt->close();
}

$conn->close();
?>
