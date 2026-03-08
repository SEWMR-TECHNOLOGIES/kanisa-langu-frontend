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
    $province_name = isset($_POST['province_name']) ? strtoupper($conn->real_escape_string($_POST['province_name'])) : '';
    $diocese_id = isset($_POST['diocese_id']) ? (int)$_POST['diocese_id'] : 0;
    $district_id = isset($_POST['district_id']) ? (int)$_POST['district_id'] : 0;
    $region_id = isset($_POST['region_id']) ? (int)$_POST['region_id'] : 0;
    $province_address = isset($_POST['province_address']) ? $conn->real_escape_string($_POST['province_address']) : '';
    $province_email = isset($_POST['province_email']) ? $conn->real_escape_string($_POST['province_email']) : '';
    $province_phone = isset($_POST['province_phone']) ? $conn->real_escape_string($_POST['province_phone']) : '';

    // Validate mandatory fields
    if (empty($province_name)) {
        echo json_encode(["success" => false, "message" => "Province name is required"]);
        exit();
    }

    if ($diocese_id == 0) {
        echo json_encode(["success" => false, "message" => "Diocese is required"]);
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

    if (empty($province_email)) {
        echo json_encode(["success" => false, "message" => "Province email is required"]);
        exit();
    }

    if (!isValidEmail($province_email)) {
        echo json_encode(["success" => false, "message" => "Invalid email address"]);
        exit();
    }

    if (empty($province_phone)) {
        echo json_encode(["success" => false, "message" => "Province phone is required"]);
        exit();
    }

    if (!isValidPhone($province_phone)) {
        echo json_encode(["success" => false, "message" => "Invalid phone number"]);
        exit();
    }

    if (empty($province_address)) {
        echo json_encode(["success" => false, "message" => "Province address is required"]);
        exit();
    }

    $sql = "INSERT INTO provinces (province_name, diocese_id, district_id, region_id, province_address, province_email, province_phone) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiisss", $province_name, $diocese_id, $district_id, $region_id, $province_address, $province_email, $province_phone);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Province registered successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to register province: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
