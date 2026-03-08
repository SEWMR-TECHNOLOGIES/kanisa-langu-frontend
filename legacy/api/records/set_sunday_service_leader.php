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
    $service_leader_id = isset($_POST['leader_id']) ? intval($_POST['leader_id']) : 0;

    // Validate service_id
    if ($service_id <= 0) {
        echo json_encode(["success" => false, "message" => "Service ID is required"]);
        exit();
    }

    // Validate service_number
    if ($service_number <= 0) {
        echo json_encode(["success" => false, "message" => "Service Number is required"]);
        exit();
    }

    // Validate service_leader_id
    if ($service_leader_id <= 0) {
        echo json_encode(["success" => false, "message" => "Leader is required"]);
        exit();
    }

    // Check if the leader is already assigned to the same service and service number
    $check_sql = "SELECT COUNT(*) as count FROM service_leaders WHERE service_id = ? AND service_number = ? AND service_leader_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("iii", $service_id, $service_number, $service_leader_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $row = $check_result->fetch_assoc();
    $check_stmt->close();

    if ($row['count'] > 0) {
        echo json_encode(["success" => false, "message" => "This leader is already assigned to this service"]);
        exit();
    }

    // Insert the leader assignment
    $sql = "INSERT INTO service_leaders (service_id, service_number, service_leader_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $service_id, $service_number, $service_leader_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Leader assigned to service successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to assign leader to service"]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
