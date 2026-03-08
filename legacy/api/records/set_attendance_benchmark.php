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
$adult_benchmark = isset($_POST['adultBenchmark']) ? (int)$_POST['adultBenchmark'] : 1000; // Default to 1000
$child_benchmark = isset($_POST['childBenchmark']) ? (int)$_POST['childBenchmark'] : 500; // Default to 500

// Ensure values are greater than zero
if ($adult_benchmark <= 0 || $child_benchmark <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid benchmark value. It must be greater than 0."]);
    exit();
}

// Check if benchmark already exists for the given head parish
$checkSql = "SELECT COUNT(*) FROM head_parish_benchmark WHERE head_parish_id = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $head_parish_id);
$checkStmt->execute();
$checkStmt->bind_result($count);
$checkStmt->fetch();
$checkStmt->close();

if ($count > 0) {
    // Update existing benchmark
    $updateSql = "UPDATE head_parish_benchmark SET adult_reading = ?, child_reading = ? WHERE head_parish_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("iii", $adult_benchmark, $child_benchmark, $head_parish_id);
    
    if ($updateStmt->execute()) {
        echo json_encode(["success" => true, "message" => "Benchmarks updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update benchmarks: " . $updateStmt->error]);
    }
    
    $updateStmt->close();
} else {
    // Insert new benchmark
    $insertSql = "INSERT INTO head_parish_benchmark (head_parish_id, adult_reading, child_reading) VALUES (?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("iii", $head_parish_id, $adult_benchmark, $child_benchmark);
    
    if ($insertStmt->execute()) {
        echo json_encode(["success" => true, "message" => "Benchmarks set successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to set benchmarks: " . $insertStmt->error]);
    }
    
    $insertStmt->close();
}

$conn->close();
?>
