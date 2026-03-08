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
    $sub_parish_name = isset($_POST['sub_parish_name']) ? strtoupper($conn->real_escape_string($_POST['sub_parish_name'])) : '';
    $description = isset($_POST['description']) && !empty(trim($_POST['description'])) ? $conn->real_escape_string($_POST['description']) : null;
    $head_parish_id = isset($_POST['head_parish_id']) ? (int)$_POST['head_parish_id'] : 0;

    // Validate mandatory fields
    if (empty($sub_parish_name)) {
        echo json_encode(["success" => false, "message" => "Sub parish name is required"]);
        exit();
    }

    if ($head_parish_id == 0) {
        echo json_encode(["success" => false, "message" => "Head parish is required"]);
        exit();
    }

    // Check for duplicate sub parish name within the same head parish
    $checkSql = "SELECT COUNT(*) FROM sub_parishes WHERE sub_parish_name = ? AND head_parish_id = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("si", $sub_parish_name, $head_parish_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo json_encode(["success" => false, "message" => "Sub parish with this name already exists"]);
        exit();
    }

    $sql = "INSERT INTO sub_parishes (sub_parish_name, description, head_parish_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $sub_parish_name, $description, $head_parish_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Sub parish added successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add sub parish: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
