<?php
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $occupation_name = isset($_POST['occupation_name']) ? $conn->real_escape_string($_POST['occupation_name']) : '';
    $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';

    if (empty($occupation_name)) {
        echo json_encode(["success" => false, "message" => "Occupation name is required"]);
        exit();
    }

    $checkSql = "SELECT COUNT(*) FROM occupations WHERE occupation_name = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $occupation_name);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo json_encode(["success" => false, "message" => "Occupation with this name already exists"]);
        exit();
    }

    $sql = "INSERT INTO occupations (occupation_name, description) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $occupation_name, $description);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Occupation registered successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to register occupation: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
