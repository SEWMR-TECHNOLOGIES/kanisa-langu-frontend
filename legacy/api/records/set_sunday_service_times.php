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
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    $service_number = isset($_POST['service_number']) ? intval($_POST['service_number']) : 0;
    $service_time = isset($_POST['service_time']) ? $_POST['service_time'] : null;

    // Validate inputs
    if ($service_id <= 0) {
        echo json_encode(["success" => false, "message" => "Service ID is required"]);
        exit();
    }

    if ($service_number <= 0) {
        echo json_encode(["success" => false, "message" => "Service number is required"]);
        exit();
    }

    if (!$service_time) {
        echo json_encode(["success" => false, "message" => "Service time is required"]);
        exit();
    }

    // Validate that the service_time is a valid time format
    $time_format = 'H:i:s';
    $service_time_obj = DateTime::createFromFormat($time_format, date('H:i:s', strtotime($service_time)));

    if (!$service_time_obj || $service_time_obj->format($time_format) !== date('H:i:s', strtotime($service_time))) {
        echo json_encode(["success" => false, "message" => "Invalid time format. Please ensure the time is in HH:MM:SS format"]);
        exit();
    }

    // Check if the record already exists
    $check_sql = "SELECT COUNT(*) as count FROM sunday_service_times WHERE service_id = ? AND service_number = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $service_id, $service_number);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $row = $check_result->fetch_assoc();
    $check_stmt->close();

    if ($row['count'] > 0) {
        // Update the existing record
        $update_sql = "UPDATE sunday_service_times SET time = ?, created_at = CURRENT_TIMESTAMP WHERE service_id = ? AND service_number = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sii", $service_time, $service_id, $service_number);

        if ($update_stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Service time updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update service time"]);
        }

        $update_stmt->close();
    } else {
        // Insert a new record
        $insert_sql = "INSERT INTO sunday_service_times (service_id, service_number, time) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iis", $service_id, $service_number, $service_time);

        if ($insert_stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Service time recorded successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to record service time"]);
        }

        $insert_stmt->close();
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
