<?php
header('Content-Type: application/json');
session_start(); // Start session to access session variables
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Check if the database connection was successful
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get head parish ID from session
    if (!isset($_SESSION['head_parish_id'])) {
        echo json_encode(["success" => false, "message" => "Head parish ID not found in session"]);
        exit();
    }
    $head_parish_id = $_SESSION['head_parish_id'];

    // Retrieve parameters from POST request
    $start_time = isset($_POST['start_time']) ? $_POST['start_time'] : '';
    $service = isset($_POST['service']) ? (int)$_POST['service'] : 0;

    // Validate service ID
    if ($service <= 0) {
        echo json_encode(["success" => false, "message" => "Please select a valid service number"]);
        exit();
    }
    
    // Validate start time format (HH:MM)
    if (!preg_match("/^([01]\d|2[0-3]):([0-5]\d)$/", $start_time)) {
        echo json_encode(["success" => false, "message" => "Invalid start time format. Use HH:MM."]);
        exit();
    }

    // Check if head parish exists
    $checkSql = "SELECT COUNT(*) FROM head_parishes WHERE head_parish_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $head_parish_id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count === 0) {
        echo json_encode(["success" => false, "message" => "Head parish not found"]);
        exit();
    }

    // Check if a service time already exists for the given service
    $checkServiceSql = "SELECT COUNT(*) FROM head_parish_services WHERE head_parish_id = ? AND service = ?";
    $checkServiceStmt = $conn->prepare($checkServiceSql);
    $checkServiceStmt->bind_param("ii", $head_parish_id, $service);
    $checkServiceStmt->execute();
    $checkServiceStmt->bind_result($existingCount);
    $checkServiceStmt->fetch();
    $checkServiceStmt->close();

    if ($existingCount > 0) {
        // If it exists, update the service time
        $updateSql = "UPDATE head_parish_services SET start_time = ? WHERE head_parish_id = ? AND service = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("sii", $start_time, $head_parish_id, $service);

        if ($updateStmt->execute()) {
            echo json_encode(["success" => true, "message" => "Service time updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update service time: " . $updateStmt->error]);
        }

        $updateStmt->close();
    } else {
        // If it doesn't exist, insert the service time
        $insertSql = "INSERT INTO head_parish_services (head_parish_id, service, start_time) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("iis", $head_parish_id, $service, $start_time);

        if ($insertStmt->execute()) {
            echo json_encode(["success" => true, "message" => "Service time registered successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to register service time: " . $insertStmt->error]);
        }

        $insertStmt->close();
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
