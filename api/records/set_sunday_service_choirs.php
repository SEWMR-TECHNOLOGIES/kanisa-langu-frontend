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
    $choir_id = isset($_POST['choir_id']) ? intval($_POST['choir_id']) : 0;

    // Validate that service_id, service_number, and choir_id are filled
    if ($service_id <= 0) {
        echo json_encode(["success" => false, "message" => "Service ID is required"]);
        exit();
    }

    if ($service_number <= 0) {
        echo json_encode(["success" => false, "message" => "Service number is required"]);
        exit();
    }

    if ($choir_id <= 0) {
        echo json_encode(["success" => false, "message" => "Choir ID is required"]);
        exit();
    }

    // Check if the combination of service_id, service_number, and choir_id already exists
    $check_sql = "SELECT COUNT(*) as count FROM service_choirs WHERE service_id = ? AND service_number = ? AND choir_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("iii", $service_id, $service_number, $choir_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $row = $check_result->fetch_assoc();
    $check_stmt->close();

    // If the combination already exists, return an error
    if ($row['count'] > 0) {
        echo json_encode(["success" => false, "message" => "This choir is already assigned to this service and service number"]);
        exit();
    }

    // Insert the choir assignment into the service_choirs table
    $sql = "INSERT INTO service_choirs (service_id, service_number, choir_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $service_id, $service_number, $choir_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Choir assigned to service successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to assign choir to service"]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
