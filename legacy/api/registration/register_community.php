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
    $community_name = isset($_POST['community_name']) ? strtoupper($conn->real_escape_string($_POST['community_name'])) : '';
    $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : null;
    $head_parish_id = isset($_POST['head_parish_id']) ? (int)$_POST['head_parish_id'] : 0;
    $sub_parish_id = isset($_POST['sub_parish_id']) ? (int)$_POST['sub_parish_id'] : 0;

    // Validate mandatory fields
    if ($head_parish_id == 0) {
        echo json_encode(["success" => false, "message" => "Head parish is required"]);
        exit();
    }
    if (empty($community_name)) {
        echo json_encode(["success" => false, "message" => "Community name is required"]);
        exit();
    }
    
    if (empty($description)) {
       $description = null;
    }

    if ($sub_parish_id == 0) {
        echo json_encode(["success" => false, "message" => "Sub parish is required"]);
        exit();
    }

    // Check for duplicate community name within the same sub parish
    $checkSql = "SELECT COUNT(*) FROM communities WHERE community_name = ? AND sub_parish_id = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("si", $community_name, $sub_parish_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo json_encode(["success" => false, "message" => "Community with this name already exists in the specified sub parish"]);
        exit();
    }

    $sql = "INSERT INTO communities (community_name, description, head_parish_id, sub_parish_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $community_name, $description, $head_parish_id, $sub_parish_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Community added successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add community: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
