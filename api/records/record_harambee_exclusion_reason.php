<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/CosineSimilarityChecker.php');
date_default_timezone_set('Africa/Nairobi');

// Check session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

// Get and validate input
$reason = trim($_POST['reason'] ?? '');

if (empty($reason)) {
    echo json_encode(["success" => false, "message" => "Exclusion reason is required."]);
    exit();
}

$newVec = CosineSimilarity::textToVector($reason);

// Check for similar existing reasons
$stmt = $conn->prepare("SELECT exclusion_reason_id, reason FROM harambee_exclusion_reasons WHERE head_parish_id = ?");
$stmt->bind_param("i", $head_parish_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $existingVec = CosineSimilarity::textToVector($row['reason']);
    $similarity = CosineSimilarity::compute($newVec, $existingVec);
    if ($similarity >= 0.8) {
        echo json_encode([
            "success" => false,
            "message" => "A similar reason already exists: '" . $row['reason'] . "'."
        ]);
        exit();
    }
}
$stmt->close();

// Insert new reason
$created_at = date('Y-m-d H:i:s');
$stmt = $conn->prepare("INSERT INTO harambee_exclusion_reasons (reason, head_parish_id, created_at) VALUES (?, ?, ?)");
$stmt->bind_param("sis", $reason, $head_parish_id, $created_at);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Exclusion reason recorded successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to record exclusion reason: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
