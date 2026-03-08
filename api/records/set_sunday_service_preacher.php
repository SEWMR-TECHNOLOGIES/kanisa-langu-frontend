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
    $service_number = isset($_POST['service_number']) ? intval($_POST['service_number']) : 0;
    $preacher_id = isset($_POST['preacher_id']) ? intval($_POST['preacher_id']) : 0;

    // Validate service_id
    if ($service_id <= 0) {
        echo json_encode(["success" => false, "message" => "Service is required"]);
        exit();
    }

    // Validate preacher_id
    if ($preacher_id <= 0) {
        echo json_encode(["success" => false, "message" => "Preacher is required"]);
        exit();
    }

    // Check if a preacher is already assigned to the specific service and service number
    $check_sql = "SELECT COUNT(*) as count FROM service_preachers WHERE service_id = ? AND service_number = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $service_id, $service_number);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $row = $check_result->fetch_assoc();
    $check_stmt->close();

    if ($row['count'] > 0) {
        echo json_encode(["success" => false, "message" => "A preacher is already assigned to this service and service number"]);
        exit();
    }

    // Insert the preacher assignment
    $sql = "INSERT INTO service_preachers (service_id, service_number, preacher_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $service_id, $service_number, $preacher_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Preacher assigned to service successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to assign preacher to service"]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
