<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(["success" => $success, "message" => $message], $extra));
    exit();
}

if (!isset($_SESSION['head_parish_id'])) {
    respond(false, "Head Parish ID is missing from session");
}

$head_parish_id = (int)$_SESSION['head_parish_id'];

if ($conn->connect_error) {
    respond(false, "Database connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, "Invalid request method");
}

$target = isset($_POST['target']) ? trim($_POST['target']) : '';
$revenue_stream_id = isset($_POST['revenue_stream_id']) ? (int)$_POST['revenue_stream_id'] : 0;
$sub_parish_id = isset($_POST['sub_parish_id']) ? (int)$_POST['sub_parish_id'] : 0;
$community_id = isset($_POST['community_id']) ? (int)$_POST['community_id'] : 0;
$group_id = isset($_POST['group_id']) ? (int)$_POST['group_id'] : 0;

if ($target === '') respond(false, "Target is required");
if ($revenue_stream_id <= 0) respond(false, "Revenue Stream is required");

$table = '';
$where = '';
$insertCols = [];
$insertTypes = '';
$insertVals = [];
$keys = []; // for select/update bindings

switch ($target) {
    case 'head-parish':
        $table = 'head_parish_revenue_stream_map';
        $where = 'head_parish_id = ? AND revenue_stream_id = ?';
        $keys = [$head_parish_id, $revenue_stream_id];

        $insertCols = ['head_parish_id', 'revenue_stream_id'];
        $insertTypes = 'ii';
        $insertVals = [$head_parish_id, $revenue_stream_id];
        break;

    case 'sub-parish':
        if ($sub_parish_id <= 0) respond(false, "Sub Parish is required");

        $table = 'sub_parish_revenue_stream_map';
        $where = 'head_parish_id = ? AND sub_parish_id = ? AND revenue_stream_id = ?';
        $keys = [$head_parish_id, $sub_parish_id, $revenue_stream_id];

        $insertCols = ['head_parish_id', 'sub_parish_id', 'revenue_stream_id'];
        $insertTypes = 'iii';
        $insertVals = [$head_parish_id, $sub_parish_id, $revenue_stream_id];
        break;

    case 'community':
        if ($sub_parish_id <= 0 || $community_id <= 0) respond(false, "Sub Parish and Community are required");

        $table = 'community_revenue_stream_map';
        $where = 'head_parish_id = ? AND sub_parish_id = ? AND community_id = ? AND revenue_stream_id = ?';
        $keys = [$head_parish_id, $sub_parish_id, $community_id, $revenue_stream_id];

        $insertCols = ['head_parish_id', 'sub_parish_id', 'community_id', 'revenue_stream_id'];
        $insertTypes = 'iiii';
        $insertVals = [$head_parish_id, $sub_parish_id, $community_id, $revenue_stream_id];
        break;

    case 'groups':
        if ($group_id <= 0) respond(false, "Group is required");

        $table = 'group_revenue_stream_map';
        $where = 'head_parish_id = ? AND group_id = ? AND revenue_stream_id = ?';
        $keys = [$head_parish_id, $group_id, $revenue_stream_id];

        $insertCols = ['head_parish_id', 'group_id', 'revenue_stream_id'];
        $insertTypes = 'iii';
        $insertVals = [$head_parish_id, $group_id, $revenue_stream_id];
        break;

    default:
        respond(false, "Invalid target");
}

/**
 * 1) Check duplicate (and whether it is active)
 */
$checkSql = "SELECT map_id, is_active FROM {$table} WHERE {$where} LIMIT 1";
$checkStmt = $conn->prepare($checkSql);
if (!$checkStmt) respond(false, "Failed to prepare duplicate check: " . $conn->error);

// Bind dynamically
$checkTypes = str_repeat('i', count($keys));
$checkStmt->bind_param($checkTypes, ...$keys);

if (!$checkStmt->execute()) {
    $checkStmt->close();
    respond(false, "Failed to check duplicates: " . $checkStmt->error);
}

$result = $checkStmt->get_result();
$existing = $result ? $result->fetch_assoc() : null;
$checkStmt->close();

if ($existing) {
    // If found and active -> duplicate
    if ((int)$existing['is_active'] === 1) {
        respond(false, "Revenue stream already linked (duplicate)", ["duplicate" => true]);
    }

    // If found but inactive -> reactivate it instead of insert
    $reactSql = "UPDATE {$table} SET is_active = 1 WHERE map_id = ?";
    $reactStmt = $conn->prepare($reactSql);
    if (!$reactStmt) respond(false, "Failed to prepare reactivate: " . $conn->error);

    $map_id = (int)$existing['map_id'];
    $reactStmt->bind_param("i", $map_id);

    if ($reactStmt->execute()) {
        $reactStmt->close();
        $conn->close();
        respond(true, "Link already existed but was inactive. Reactivated successfully.", ["reactivated" => true]);
    } else {
        $err = $reactStmt->error;
        $reactStmt->close();
        respond(false, "Failed to reactivate mapping: " . $err);
    }
}

/**
 * 2) If no existing row, insert new
 */
$cols = implode(', ', $insertCols);
$placeholders = implode(', ', array_fill(0, count($insertCols), '?'));
$insertSql = "INSERT INTO {$table} ({$cols}) VALUES ({$placeholders})";

$insStmt = $conn->prepare($insertSql);
if (!$insStmt) respond(false, "Failed to prepare insert: " . $conn->error);

$insStmt->bind_param($insertTypes, ...$insertVals);

if ($insStmt->execute()) {
    $insStmt->close();
    $conn->close();
    respond(true, "Link saved successfully");
}

// Fallback: if race condition happens, unique key can still trigger duplicate
if ($conn->errno == 1062) {
    $insStmt->close();
    $conn->close();
    respond(false, "Revenue stream already linked (duplicate)", ["duplicate" => true]);
}

$err = $insStmt->error;
$insStmt->close();
$conn->close();
respond(false, "Failed to save mapping: " . $err);
?>
