<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Ensure the head_parish_id is set in session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    $elder_id = isset($_POST['elder_id']) ? intval($_POST['elder_id']) : 0;
    $service_number = isset($_POST['service_number']) ? intval($_POST['service_number']) : 0;

    // Validate required fields
    if ($service_id <= 0) {
        echo json_encode(["success" => false, "message" => "Service is required"]);
        exit();
    }

    if ($elder_id <= 0) {
        echo json_encode(["success" => false, "message" => "Elder is required"]);
        exit();
    }

    if ($service_number <= 0) {
        echo json_encode(["success" => false, "message" => "Service number is required"]);
        exit();
    }

    // Check if the elder is already assigned to this service and service number
    $check_sql = "SELECT COUNT(*) as count FROM service_elders WHERE service_id = ? AND elder_id = ? AND service_number = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("iii", $service_id, $elder_id, $service_number);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $row = $check_result->fetch_assoc();
    $check_stmt->close();

    // If the combination of service_id, elder_id, and service_number already exists, return an error
    if ($row['count'] > 0) {
        echo json_encode(["success" => false, "message" => "This elder is already assigned to the service number"]);
        exit();
    }

    // Insert the elder assignment into the service_elders table
    $sql = "INSERT INTO service_elders (service_id, elder_id, service_number) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $service_id, $elder_id, $service_number);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Elder assigned to service successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to assign elder to service"]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
