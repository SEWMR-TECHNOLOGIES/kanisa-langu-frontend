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
    $district_name = isset($_POST['district_name']) ? $conn->real_escape_string($_POST['district_name']) : '';
    $region_id = isset($_POST['region_id']) ? (int)$_POST['region_id'] : 0;

    // Validate mandatory fields
    if (empty($district_name)) {
        echo json_encode(["success" => false, "message" => "District name is required"]);
        exit();
    }

    if ($region_id == 0) {
        echo json_encode(["success" => false, "message" => "Region is required"]);
        exit();
    }

    // Check if the district already exists
    $checkSql = "SELECT COUNT(*) FROM districts WHERE district_name = ? AND region_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("si", $district_name, $region_id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo json_encode(["success" => false, "message" => "District with this name already exists in the specified region"]);
        exit();
    }

    // Insert the new district
    $sql = "INSERT INTO districts (district_name, region_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $district_name, $region_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "District registered successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to register district: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
