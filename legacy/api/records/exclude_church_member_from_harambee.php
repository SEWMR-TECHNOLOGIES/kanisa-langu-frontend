<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
date_default_timezone_set('Africa/Nairobi');

// Verify session variables
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
$harambee_id = $_POST['harambee_id'] ?? null;
$member_id = $_POST['member_id'] ?? null;
$exclusion_reason = $_POST['exclusion_reason'] ?? null;
$target = $_POST['target'] ?? null;

$allowed_targets = ['head-parish', 'sub-parish', 'community', 'group'];

// User-friendly error messages
if (empty($harambee_id) || !ctype_digit($harambee_id)) {
    echo json_encode(["success" => false, "message" => "Please select a valid Harambee."]);
    exit();
}
if (empty($member_id) || !ctype_digit($member_id)) {
    echo json_encode(["success" => false, "message" => "Please choose a member to exclude."]);
    exit();
}
if (empty($exclusion_reason) || !ctype_digit($exclusion_reason)) {
    echo json_encode(["success" => false, "message" => "Please choose a reason for exclusion."]);
    exit();
}
if (empty($target) || !in_array($target, $allowed_targets)) {
    echo json_encode(["success" => false, "message" => "Please select a valid exclusion target."]);
    exit();
}


$harambee_id = intval($harambee_id);
$member_id = intval($member_id);
$exclusion_reason = intval($exclusion_reason);

// Check for duplicate exclusion
$checkSql = "SELECT exclusion_id FROM harambee_exclusions 
             WHERE harambee_id = ? AND member_id = ? AND harambee_target = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("iis", $harambee_id, $member_id, $target);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "This member is already excluded for this Harambee target."]);
    $checkStmt->close();
    exit();
}
$checkStmt->close();

// Insert exclusion
$excluded_datetime = date('Y-m-d H:i:s');
$insertSql = "INSERT INTO harambee_exclusions 
    (harambee_target, harambee_id, member_id, exclusion_reason_id, excluded_datetime, excluded_by, head_parish_id)
    VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($insertSql);
$stmt->bind_param("siissii", $target, $harambee_id, $member_id, $exclusion_reason, $excluded_datetime, $excluded_by, $head_parish_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Member excluded successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
