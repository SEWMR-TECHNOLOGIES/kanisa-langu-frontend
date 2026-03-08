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
    // Retrieve and sanitize input values
    $head_parish_name = isset($_POST['head_parish_name']) ? strtoupper($conn->real_escape_string($_POST['head_parish_name'])) : '';
    $diocese_id = isset($_POST['diocese_id']) ? (int)$_POST['diocese_id'] : 0;
    $province_id = isset($_POST['province_id']) ? (int)$_POST['province_id'] : 0;
    $region_id = isset($_POST['region_id']) ? (int)$_POST['region_id'] : 0;
    $district_id = isset($_POST['district_id']) ? (int)$_POST['district_id'] : 0;
    $head_parish_address = isset($_POST['head_parish_address']) ? $conn->real_escape_string($_POST['head_parish_address']) : '';
    $head_parish_email = isset($_POST['head_parish_email']) ? $conn->real_escape_string($_POST['head_parish_email']) : '';
    $head_parish_phone = isset($_POST['head_parish_phone']) ? $conn->real_escape_string($_POST['head_parish_phone']) : '';

    // Validate mandatory fields
    if (empty($head_parish_name)) {
        echo json_encode(["success" => false, "message" => "Head parish name is required"]);
        exit();
    }

    if ($diocese_id == 0) {
        echo json_encode(["success" => false, "message" => "Diocese is required"]);
        exit();
    }

    if ($province_id == 0) {
        echo json_encode(["success" => false, "message" => "Province is required"]);
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

    if (empty($head_parish_email)) {
        echo json_encode(["success" => false, "message" => "Head parish email is required"]);
        exit();
    }

    if (!isValidEmail($head_parish_email)) {
        echo json_encode(["success" => false, "message" => "Invalid email address"]);
        exit();
    }

    if (empty($head_parish_phone)) {
        echo json_encode(["success" => false, "message" => "Head parish phone is required"]);
        exit();
    }

    if (!isValidPhone($head_parish_phone)) {
        echo json_encode(["success" => false, "message" => "Invalid phone number"]);
        exit();
    }

    if (empty($head_parish_address)) {
        echo json_encode(["success" => false, "message" => "Head parish address is required"]);
        exit();
    }

    // Check for existing head parish with the same name in the same diocese and province
    $checkSql = "SELECT * FROM head_parishes WHERE head_parish_name = ? AND diocese_id = ? AND province_id = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("sii", $head_parish_name, $diocese_id, $province_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "A head parish with this name already exists in the selected diocese and province"]);
        $stmt->close();
        exit();
    }

    // Insert new head parish record
    $sql = "INSERT INTO head_parishes (head_parish_name, head_parish_address, head_parish_email, head_parish_phone, diocese_id, province_id, region_id, district_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssiiii", $head_parish_name, $head_parish_address, $head_parish_email, $head_parish_phone, $diocese_id, $province_id, $region_id, $district_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Head parish registered successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to register head parish: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
