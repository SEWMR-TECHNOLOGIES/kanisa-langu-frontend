<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Check session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

// Inputs
$class_name = strtoupper(trim($_POST['class_name'] ?? ''));
$amount_min = $_POST['amount_min'] ?? null;
$amount_max = $_POST['amount_max'] ?? null;

// Validate class name
if (!preg_match('/^[A-Z]$/', $class_name)) {
    echo json_encode(["success" => false, "message" => "Invalid class name. Only A-Z are allowed."]);
    exit();
}

// Validate amount_min
if (!is_numeric($amount_min)) {
    echo json_encode(["success" => false, "message" => "Minimum amount must be a valid number."]);
    exit();
}

// Class sequencing
$prev_class = chr(ord($class_name) - 1);
if ($class_name !== 'A') {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM head_parish_harambee_classes WHERE class_name = ? AND head_parish_id = ?");
    $stmt->bind_param("si", $prev_class, $head_parish_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res['count'] == 0) {
        echo json_encode(["success" => false, "message" => "Cannot record class $class_name before class $prev_class is recorded."]);
        exit();
    }
    $stmt->close();
}

// Check for existing class
$stmt = $conn->prepare("SELECT harambee_class_id FROM head_parish_harambee_classes WHERE class_name = ? AND head_parish_id = ?");
$stmt->bind_param("si", $class_name, $head_parish_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update
    $row = $result->fetch_assoc();
    $stmt = $conn->prepare("UPDATE head_parish_harambee_classes SET amount_min = ?, amount_max = ? WHERE harambee_class_id = ?");
    $stmt->bind_param("ddi", $amount_min, $amount_max, $row['harambee_class_id']);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Class $class_name updated successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update class: " . $stmt->error]);
    }
    $stmt->close();
} else {
    // Insert
    $stmt = $conn->prepare("INSERT INTO head_parish_harambee_classes (head_parish_id, class_name, amount_min, amount_max) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isdd", $head_parish_id, $class_name, $amount_min, $amount_max);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Class $class_name recorded successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to record class: " . $stmt->error]);
    }
    $stmt->close();
}

$conn->close();

?>