<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/validation_functions.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diocese_name = isset($_POST['diocese_name']) ? strtoupper($conn->real_escape_string($_POST['diocese_name'])) : '';
    $region_id = isset($_POST['region_id']) ? (int)$_POST['region_id'] : 0;
    $district_id = isset($_POST['district_id']) ? (int)$_POST['district_id'] : 0;
    $diocese_address = isset($_POST['diocese_address']) ? $conn->real_escape_string($_POST['diocese_address']) : null;
    $diocese_email = isset($_POST['diocese_email']) ? $conn->real_escape_string($_POST['diocese_email']) : null;
    $diocese_phone = isset($_POST['diocese_phone']) ? $conn->real_escape_string($_POST['diocese_phone']) : null;

    if (empty($diocese_name)) {
        echo json_encode(["success" => false, "message" => "Diocese name is required"]);
        exit();
    }

    if (empty($diocese_email)) {
        echo json_encode(["success" => false, "message" => "Diocese email is required"]);
        exit();
    }

    if (!isValidEmail($diocese_email)) {
        echo json_encode(["success" => false, "message" => "Invalid email address"]);
        exit();
    }

    if (empty($diocese_phone)) {
        echo json_encode(["success" => false, "message" => "Diocese phone number is required"]);
        exit();
    }

    if (!isValidPhone($diocese_phone)) {
        echo json_encode(["success" => false, "message" => "Invalid phone number"]);
        exit();
    }

    if (empty($diocese_address)) {
        echo json_encode(["success" => false, "message" => "Diocese address is required"]);
        exit();
    }

    if ($region_id == 0) {
        echo json_encode(["success" => false, "message" => "Region is required"]);
        exit();
    }
    
    if ($district_id == 0) {
        echo json_encode(["success" => false, "message" => "District is required"]);
        exit();
    }
    // Check if the diocese already exists
    $checkSql = "SELECT COUNT(*) FROM dioceses WHERE diocese_name = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $diocese_name);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo json_encode(["success" => false, "message" => "Diocese with this name already exists"]);
        exit();
    }

    // Insert the new diocese
    $sql = "INSERT INTO dioceses (diocese_name, district_id, region_id, diocese_address, diocese_email, diocese_phone) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siisss", $diocese_name, $district_id, $region_id, $diocese_address, $diocese_email, $diocese_phone);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Diocese registered successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to register diocese: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
