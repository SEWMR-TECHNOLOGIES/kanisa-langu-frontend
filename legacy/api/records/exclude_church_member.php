<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
date_default_timezone_set('Africa/Nairobi');

// Verify session
if (!isset($_SESSION['head_parish_admin_id']) || !isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Login session missing."]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];
$excluded_by = $_SESSION['head_parish_admin_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit();
}

// Clean and validate input
$member_id = $_POST['member_id'] ?? null;
$exclusion_reason_id = $_POST['exclusion_reason'] ?? null;

if (empty($member_id) || !ctype_digit($member_id)) {
    echo json_encode(["success" => false, "message" => "Please choose a member to exclude."]);
    exit();
}
if (empty($exclusion_reason_id) || !ctype_digit($exclusion_reason_id)) {
    echo json_encode(["success" => false, "message" => "Please choose a reason for exclusion."]);
    exit();
}

$member_id = intval($member_id);
$exclusion_reason_id = intval($exclusion_reason_id);

// Check if member is already excluded
$checkSql = "SELECT exclusion_id FROM church_member_exclusions 
             WHERE member_id = ? AND head_parish_id = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("ii", $member_id, $head_parish_id);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    echo json_encode([
        "success" => false, 
        "message" => "This member is already excluded from church operations."
    ]);
    $checkStmt->close();
    exit();
}
$checkStmt->close();

// Insert exclusion
$excluded_datetime = date('Y-m-d H:i:s');
$insertSql = "INSERT INTO church_member_exclusions 
    (member_id, exclusion_reason_id, excluded_datetime, excluded_by, head_parish_id)
    VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($insertSql);
$stmt->bind_param("iisii", $member_id, $exclusion_reason_id, $excluded_datetime, $excluded_by, $head_parish_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Member excluded successfully. Excluded members will not be able to participate in church operations such as harambee, envelopes, and other daily church activities."]);
} else {
    echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
