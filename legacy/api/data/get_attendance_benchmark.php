<?php
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Check if head parish ID is set
session_start();
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access."]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

// Fetch current benchmarks for both adult and child
$sql = "SELECT adult_reading, child_reading FROM head_parish_benchmark WHERE head_parish_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $head_parish_id);
$stmt->execute();
$stmt->bind_result($adult_reading, $child_reading);
$stmt->fetch();
$stmt->close();

if ($adult_reading !== null && $child_reading !== null) {
    echo json_encode([
        "success" => true, 
        "adult_reading" => $adult_reading, 
        "child_reading" => $child_reading
    ]);
} else {
    echo json_encode(["success" => false, "message" => "No benchmark set"]);
}

$conn->close();
?>
