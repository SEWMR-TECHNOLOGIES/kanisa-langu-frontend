<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
date_default_timezone_set('Africa/Nairobi');

// Session check
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

// Request check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

// Input validation
$reason_id = intval($_POST['reason_id'] ?? 0);
if ($reason_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid or missing reason ID."]);
    exit();
}

// Check if reason exists and belongs to this head parish
$stmt = $conn->prepare("SELECT reason FROM harambee_exclusion_reasons WHERE exclusion_reason_id = ? AND head_parish_id = ?");
$stmt->bind_param("ii", $reason_id, $head_parish_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Reason not found or not accessible."]);
    exit();
}

$reason_row = $result->fetch_assoc();
$stmt->close();

// Check if reason is in use
$stmt = $conn->prepare("SELECT COUNT(*) AS usage_count FROM harambee_exclusions WHERE exclusion_reason_id = ?");
$stmt->bind_param("i", $reason_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($res['usage_count'] > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Cannot delete. Reason '" . $reason_row['reason'] . "' is being used by excluded members."
    ]);
    exit();
}

// Safe to delete
$stmt = $conn->prepare("DELETE FROM harambee_exclusion_reasons WHERE exclusion_reason_id = ?");
$stmt->bind_param("i", $reason_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Reason deleted successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Deletion failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
