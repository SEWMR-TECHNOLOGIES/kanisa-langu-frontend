<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/validation_functions.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $region_name = isset($_POST['region_name']) ? $conn->real_escape_string($_POST['region_name']) : '';

    // Validate mandatory fields
    if (empty($region_name)) {
        echo json_encode(["success" => false, "message" => "Region name is required"]);
        exit();
    }

    // Check if the region already exists
    $checkSql = "SELECT COUNT(*) FROM regions WHERE region_name = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $region_name);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo json_encode(["success" => false, "message" => "Region with this name already exists"]);
        exit();
    }

    // Insert the new region
    $sql = "INSERT INTO regions (region_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $region_name);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Region registered successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to register region: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
