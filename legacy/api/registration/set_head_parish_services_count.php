<?php
header('Content-Type: application/json');
session_start(); // Start session to access session variables
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Check if the database connection was successful
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get head parish ID from session
    if (!isset($_SESSION['head_parish_id'])) {
        echo json_encode(["success" => false, "message" => "Head parish ID not found in session"]);
        exit();
    }
    $head_parish_id = $_SESSION['head_parish_id'];

    // Retrieve services count from POST request
    $services_count = isset($_POST['services_count']) ? (int)$_POST['services_count'] : 0;

    if (empty($services_count)) {
        echo json_encode(["success" => false, "message" => "Invalid services count"]);
        exit();
    }
    
    // Validate services count
    if ($services_count < 0) {
        echo json_encode(["success" => false, "message" => "Invalid services count"]);
        exit();
    }

    // Check if head parish exists
    $checkSql = "SELECT COUNT(*) FROM head_parishes WHERE head_parish_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $head_parish_id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count === 0) {
        echo json_encode(["success" => false, "message" => "Head parish not found"]);
        exit();
    }

    // Update the services count for the head parish
    $updateSql = "UPDATE head_parishes SET services_count = ? WHERE head_parish_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ii", $services_count, $head_parish_id);

    if ($updateStmt->execute()) {
        echo json_encode(["success" => true, "message" => "Services count updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update services count: " . $updateStmt->error]);
    }

    $updateStmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
