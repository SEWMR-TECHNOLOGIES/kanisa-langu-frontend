<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/validation_functions.php');

// Check the database connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bank_name = isset($_POST['bank_name']) ? $conn->real_escape_string($_POST['bank_name']) : '';

    // Validate the input
    if (empty($bank_name)) {
        echo json_encode(["success" => false, "message" => "Bank name is required"]);
        exit();
    }

    // Check if the bank already exists
    $checkSql = "SELECT COUNT(*) FROM banks WHERE bank_name = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $bank_name);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo json_encode(["success" => false, "message" => "Bank with this name already exists"]);
        exit();
    }

    // Insert the new bank into the banks table
    $sql = "INSERT INTO banks (bank_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $bank_name);

    // Check if the insert was successful
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Bank created successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to create bank: " . $stmt->error]);
    }

    // Close the statement
    $stmt->close();
} else {
    // If the request method is not POST
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
