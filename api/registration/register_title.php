<?php
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
    $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';

    // Validate mandatory fields
    if (empty($name)) {
        echo json_encode(["success" => false, "message" => "Title name is required"]);
        exit();
    }

    if (empty($description)) {
        echo json_encode(["success" => false, "message" => "Description is required"]);
        exit();
    }

    // Check if the title already exists
    $checkSql = "SELECT COUNT(*) FROM titles WHERE name = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $name);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo json_encode(["success" => false, "message" => "Title with this name already exists"]);
        exit();
    }

    // Insert the new title
    $sql = "INSERT INTO titles (name, description) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $name, $description);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Title registered successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to register title: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
