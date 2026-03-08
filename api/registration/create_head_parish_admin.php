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
    $head_parish_admin_fullname = isset($_POST['head_parish_admin_fullname']) ? $conn->real_escape_string($_POST['head_parish_admin_fullname']) : '';
    $head_parish_admin_email = isset($_POST['head_parish_admin_email']) ? $conn->real_escape_string($_POST['head_parish_admin_email']) : '';
    $head_parish_admin_phone = isset($_POST['head_parish_admin_phone']) ? $conn->real_escape_string($_POST['head_parish_admin_phone']) : '';
    $head_parish_admin_role = isset($_POST['head_parish_admin_role']) ? $conn->real_escape_string($_POST['head_parish_admin_role']) : '';
    $head_parish_id = isset($_POST['head_parish_id']) ? (int)$_POST['head_parish_id'] : 0;

    // Default hashed password (bcrypt) if no password is provided
    $default_password = password_hash('KanisaLangu', PASSWORD_BCRYPT);

    // Validate mandatory fields
    if (empty($head_parish_admin_fullname)) {
        echo json_encode(["success" => false, "message" => "Admin fullname is required"]);
        exit();
    }

    if (empty($head_parish_admin_email) || !isValidEmail($head_parish_admin_email)) {
        echo json_encode(["success" => false, "message" => "A valid email is required"]);
        exit();
    }

    if (empty($head_parish_admin_phone) || !isValidPhone($head_parish_admin_phone)) {
        echo json_encode(["success" => false, "message" => "A valid phone number is required"]);
        exit();
    }

    if (!in_array($head_parish_admin_role, ['admin', 'pastor', 'secretary', 'chairperson'])) {
        echo json_encode(["success" => false, "message" => "Invalid admin role"]);
        exit();
    }

    if ($head_parish_id == 0) {
        echo json_encode(["success" => false, "message" => "Head parish is required"]);
        exit();
    }

    // Check for existing email or phone number
    $checkSql = "SELECT head_parish_admin_id FROM head_parish_admins WHERE head_parish_admin_email = ? OR head_parish_admin_phone = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ss", $head_parish_admin_email, $head_parish_admin_phone);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Email or phone number already exists"]);
        $checkStmt->close();
        exit();
    }
    $checkStmt->close();

    // Insert query
    $sql = "INSERT INTO head_parish_admins (head_parish_admin_fullname, head_parish_admin_email, head_parish_admin_phone, head_parish_admin_role, head_parish_admin_password, head_parish_id) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $head_parish_admin_fullname, $head_parish_admin_email, $head_parish_admin_phone, $head_parish_admin_role, $default_password, $head_parish_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Head parish admin registered successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to register head parish admin: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
