<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
date_default_timezone_set('Africa/Nairobi');

// Check if session is active
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Session expired. Please login again."]);
    exit();
}
$head_parish_id = $_SESSION['head_parish_id'];

// Ensure POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit();
}

// Validate exclusion_id
$exclusion_id = intval($_POST['exclusion_id'] ?? 0);
if ($exclusion_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid or missing exclusion ID."]);
    exit();
}

// Check if exclusion exists and belongs to the head parish
$stmt = $conn->prepare("SELECT exclusion_id FROM harambee_exclusions WHERE exclusion_id = ? AND head_parish_id = ?");
$stmt->bind_param("ii", $exclusion_id, $head_parish_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Excluded member not found or access denied."]);
    exit();
}
$stmt->close();

// Perform deletion
$stmt = $conn->prepare("DELETE FROM harambee_exclusions WHERE exclusion_id = ?");
$stmt->bind_param("i", $exclusion_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Excluded member removed successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Deletion failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
