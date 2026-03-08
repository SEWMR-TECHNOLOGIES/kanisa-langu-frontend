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
    // Gather and sanitize POST data
    $diocese_admin_fullname = isset($_POST['diocese_admin_fullname']) ? $conn->real_escape_string($_POST['diocese_admin_fullname']) : '';
    $diocese_admin_email = isset($_POST['diocese_admin_email']) ? $conn->real_escape_string($_POST['diocese_admin_email']) : '';
    $diocese_admin_phone = isset($_POST['diocese_admin_phone']) ? $conn->real_escape_string($_POST['diocese_admin_phone']) : '';
    $diocese_admin_role = isset($_POST['diocese_admin_role']) ? $conn->real_escape_string($_POST['diocese_admin_role']) : '';
    $diocese_id = isset($_POST['diocese_id']) ? (int)$_POST['diocese_id'] : 0;

    // Default hashed password (bcrypt) if no password is provided
    $default_password = password_hash('KanisaLangu', PASSWORD_BCRYPT);

    // Validate mandatory fields
    if (empty($diocese_admin_fullname)) {
        echo json_encode(["success" => false, "message" => "Admin fullname is required"]);
        exit();
    }

    if (empty($diocese_admin_email) || !isValidEmail($diocese_admin_email)) {
        echo json_encode(["success" => false, "message" => "A valid email is required"]);
        exit();
    }

    if (empty($diocese_admin_phone) || !isValidPhone($diocese_admin_phone)) {
        echo json_encode(["success" => false, "message" => "A valid phone number is required"]);
        exit();
    }

    if (!in_array($diocese_admin_role, ['admin', 'bishop', 'secretary', 'chairperson'])) {
        echo json_encode(["success" => false, "message" => "Invalid admin role"]);
        exit();
    }

    if ($diocese_id == 0) {
        echo json_encode(["success" => false, "message" => "Diocese is required"]);
        exit();
    }

    // Check for existing email or phone number
    $checkSql = "SELECT diocese_admin_id FROM diocese_admins WHERE diocese_admin_email = ? OR diocese_admin_phone = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ss", $diocese_admin_email, $diocese_admin_phone);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Email or phone number already exists"]);
        $checkStmt->close();
        exit();
    }
    $checkStmt->close();

    // Insert query
    $sql = "INSERT INTO diocese_admins (diocese_admin_fullname, diocese_admin_email, diocese_admin_phone, diocese_admin_role, diocese_admin_password, diocese_id) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $diocese_admin_fullname, $diocese_admin_email, $diocese_admin_phone, $diocese_admin_role, $default_password, $diocese_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Diocese admin registered successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to register diocese admin: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
