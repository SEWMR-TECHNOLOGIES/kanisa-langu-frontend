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
    $province_admin_fullname = isset($_POST['province_admin_fullname']) ? $conn->real_escape_string($_POST['province_admin_fullname']) : '';
    $province_admin_email = isset($_POST['province_admin_email']) ? $conn->real_escape_string($_POST['province_admin_email']) : '';
    $province_admin_phone = isset($_POST['province_admin_phone']) ? $conn->real_escape_string($_POST['province_admin_phone']) : '';
    $province_admin_role = isset($_POST['province_admin_role']) ? $conn->real_escape_string($_POST['province_admin_role']) : '';
    $province_id = isset($_POST['province_id']) ? (int)$_POST['province_id'] : 0;

    // Default hashed password (bcrypt) if no password is provided
    $default_password = password_hash('KanisaLangu', PASSWORD_BCRYPT);

    // Validate mandatory fields
    if (empty($province_admin_fullname)) {
        echo json_encode(["success" => false, "message" => "Admin fullname is required"]);
        exit();
    }

    if (empty($province_admin_email) || !isValidEmail($province_admin_email)) {
        echo json_encode(["success" => false, "message" => "A valid email is required"]);
        exit();
    }

    if (empty($province_admin_phone) || !isValidPhone($province_admin_phone)) {
        echo json_encode(["success" => false, "message" => "A valid phone number is required"]);
        exit();
    }

    if (!in_array($province_admin_role, ['admin', 'bishop', 'secretary', 'chairperson'])) {
        echo json_encode(["success" => false, "message" => "Invalid admin role"]);
        exit();
    }

    if ($province_id == 0) {
        echo json_encode(["success" => false, "message" => "Province is required"]);
        exit();
    }

    // Check for existing email or phone number
    $checkSql = "SELECT province_admin_id FROM province_admins WHERE province_admin_email = ? OR province_admin_phone = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ss", $province_admin_email, $province_admin_phone);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Email or phone number already exists"]);
        $checkStmt->close();
        exit();
    }
    $checkStmt->close();

    // Insert query
    $sql = "INSERT INTO province_admins (province_admin_fullname, province_admin_email, province_admin_phone, province_admin_role, province_admin_password, province_id) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $province_admin_fullname, $province_admin_email, $province_admin_phone, $province_admin_role, $default_password, $province_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Province admin registered successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to register province admin: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
