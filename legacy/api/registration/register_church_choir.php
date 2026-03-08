<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Ensure the head_parish_id is set in the session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $choir_name = isset($_POST['choir_name']) ? $conn->real_escape_string($_POST['choir_name']) : null;

    if (empty($choir_name)) {
        echo json_encode(["success" => false, "message" => "Choir name is required"]);
        exit();
    }

    // Check if the choir name already exists in the given parish
    $check_sql = "SELECT choir_id FROM church_choirs WHERE choir_name = ? AND head_parish_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $choir_name, $head_parish_id);
    $check_stmt->execute();
    $check_stmt->bind_result($choir_id);
    $record_exists = $check_stmt->fetch();
    $check_stmt->close();

    if ($record_exists) {
        echo json_encode(["success" => false, "message" => "Choir name already exists in this parish"]);
        exit();
    }

    // Insert new choir record
    $insert_sql = "INSERT INTO church_choirs (choir_name, head_parish_id) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("si", $choir_name, $head_parish_id);

    if ($insert_stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Church choir registered successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to register church choir"]);
    }

    $insert_stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
