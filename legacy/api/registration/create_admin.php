<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/validation_functions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Ensure head_parish_id is in session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = (int)$_SESSION['head_parish_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

// Gather and sanitize POST data
$admin_fullname = isset($_POST['admin_fullname']) ? trim($_POST['admin_fullname']) : '';
$admin_email    = isset($_POST['admin_email']) ? trim($_POST['admin_email']) : '';
$admin_phone    = isset($_POST['admin_phone']) ? trim($_POST['admin_phone']) : '';
$admin_role     = isset($_POST['admin_role']) ? trim($_POST['admin_role']) : '';
$target         = isset($_POST['target']) ? trim($_POST['target']) : '';

// Default hashed password (bcrypt) if no password is provided
$default_password = password_hash('KanisaLangu', PASSWORD_BCRYPT);

// Validate mandatory fields
if ($admin_fullname === '') {
    echo json_encode(["success" => false, "message" => "Admin fullname is required"]);
    exit();
}

// For head-parish email is mandatory; others optional in your original logic
if ((($admin_email === '') || !isValidEmail($admin_email)) && $target === 'head-parish') {
    echo json_encode(["success" => false, "message" => "A valid email is required"]);
    exit();
}

if ($admin_phone === '' || !isValidPhone($admin_phone)) {
    echo json_encode(["success" => false, "message" => "A valid phone number is required"]);
    exit();
}

if (!in_array($admin_role, ['admin','secretary','chairperson','accountant','clerk','pastor','evangelist','elder'], true)) {
    echo json_encode(["success" => false, "message" => "Invalid admin role"]);
    exit();
}

if ($head_parish_id <= 0) {
    echo json_encode(["success" => false, "message" => "Head parish is required"]);
    exit();
}

// Determine table and columns based on the target
$table = "";
$email_column = "";
$phone_column = "";

// These are the “scope columns” used for duplicate checks
// (head-parish scope is always head_parish_id)
$scopeWhereSql = " head_parish_id = ? ";
$scopeBindTypes = "i";
$scopeBindValues = [$head_parish_id];

// Will be filled if target needs extra IDs
$sub_parish_id = null;
$community_id = null;
$group_id = null;

switch ($target) {
    case 'head-parish':
        $table = "head_parish_admins";
        $email_column = "head_parish_admin_email";
        $phone_column = "head_parish_admin_phone";
        // scope = head_parish_id only (already set)
        break;

    case 'sub-parish':
        $table = "sub_parish_admins";
        $email_column = "sub_parish_admin_email";
        $phone_column = "sub_parish_admin_phone";

        $sub_parish_id = isset($_POST['sub_parish_id']) ? (int)$_POST['sub_parish_id'] : 0;
        if ($sub_parish_id <= 0) {
            echo json_encode(["success" => false, "message" => "Sub Parish is required"]);
            exit();
        }

        $scopeWhereSql .= " AND sub_parish_id = ? ";
        $scopeBindTypes .= "i";
        $scopeBindValues[] = $sub_parish_id;
        break;

    case 'community':
        $table = "community_admins";
        $email_column = "community_admin_email";
        $phone_column = "community_admin_phone";

        $sub_parish_id = isset($_POST['sub_parish_id']) ? (int)$_POST['sub_parish_id'] : 0;
        $community_id  = isset($_POST['community_id']) ? (int)$_POST['community_id'] : 0;

        if ($sub_parish_id <= 0 || $community_id <= 0) {
            echo json_encode(["success" => false, "message" => "Sub Parish and Community are required"]);
            exit();
        }

        $scopeWhereSql .= " AND sub_parish_id = ? AND community_id = ? ";
        $scopeBindTypes .= "ii";
        $scopeBindValues[] = $sub_parish_id;
        $scopeBindValues[] = $community_id;
        break;

    case 'group':
        $table = "group_admins";
        $email_column = "group_admin_email";
        $phone_column = "group_admin_phone";

        $group_id = isset($_POST['group_id']) ? (int)$_POST['group_id'] : 0;
        if ($group_id <= 0) {
            echo json_encode(["success" => false, "message" => "Group is required"]);
            exit();
        }

        $scopeWhereSql .= " AND group_id = ? ";
        $scopeBindTypes .= "i";
        $scopeBindValues[] = $group_id;
        break;

    default:
        echo json_encode(["success" => false, "message" => "Invalid target"]);
        exit();
}

/**
 * ✅ DUPLICATE CHECKS (SPECIFIC TO SCOPE)
 * - head-parish: duplicates checked within same head_parish_id
 * - sub-parish: duplicates checked within same head_parish_id AND sub_parish_id
 * - community: duplicates checked within same head_parish_id AND sub_parish_id AND community_id
 * - group: duplicates checked within same head_parish_id AND group_id
 */

// Email duplicate check (only if email provided)
if ($admin_email !== '') {
    if (!isValidEmail($admin_email)) {
        echo json_encode(["success" => false, "message" => "Invalid email format"]);
        exit();
    }

    $checkEmailSql = "SELECT 1 FROM $table WHERE $email_column = ? AND $scopeWhereSql LIMIT 1";
    $checkEmailStmt = $conn->prepare($checkEmailSql);
    if (!$checkEmailStmt) {
        echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
        exit();
    }

    // build bind: "s" + scope types
    $emailBindTypes = "s" . $scopeBindTypes;
    $emailBindValues = array_merge([$admin_email], $scopeBindValues);

    $checkEmailStmt->bind_param($emailBindTypes, ...$emailBindValues);
    $checkEmailStmt->execute();
    $emailResult = $checkEmailStmt->get_result();

    if ($emailResult->num_rows > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Email already exists for this " . $target . " (same scope)"
        ]);
        $checkEmailStmt->close();
        exit();
    }
    $checkEmailStmt->close();
}

// Phone duplicate check (always)
$checkPhoneSql = "SELECT 1 FROM $table WHERE $phone_column = ? AND $scopeWhereSql LIMIT 1";
$checkPhoneStmt = $conn->prepare($checkPhoneSql);
if (!$checkPhoneStmt) {
    echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
    exit();
}

$phoneBindTypes = "s" . $scopeBindTypes;
$phoneBindValues = array_merge([$admin_phone], $scopeBindValues);

$checkPhoneStmt->bind_param($phoneBindTypes, ...$phoneBindValues);
$checkPhoneStmt->execute();
$phoneResult = $checkPhoneStmt->get_result();

if ($phoneResult->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Phone number already exists for this " . $target . " (same scope)"
    ]);
    $checkPhoneStmt->close();
    exit();
}
$checkPhoneStmt->close();

// ---------- SIGNATURE UPLOAD ----------
$signature_path_name = null;

// Check if signature is uploaded
if ((!isset($_FILES['signature_path']) || $_FILES['signature_path']['error'] === UPLOAD_ERR_NO_FILE) && $target === 'head-parish') {
    if ($admin_role !== 'clerk') {
        echo json_encode(["success" => false, "message" => "Please select a file to upload."]);
        exit();
    }
} else if ($target === 'head-parish' && isset($_FILES['signature_path']) && $_FILES['signature_path']['error'] !== UPLOAD_ERR_NO_FILE) {

    $signature_path = $_FILES['signature_path'];
    $allowed_types = ['image/jpeg', 'image/png'];
    $max_file_size = 2 * 1024 * 1024; // 2MB

    if (!in_array($signature_path['type'], $allowed_types, true)) {
        echo json_encode(["success" => false, "message" => "Invalid file type. Only JPEG and PNG are allowed."]);
        exit();
    }

    if ($signature_path['size'] > $max_file_size) {
        echo json_encode(["success" => false, "message" => "File size exceeds 2MB limit."]);
        exit();
    }

    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/signatures/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $signature_path_name = uniqid('signature_', true) . '.' . pathinfo($signature_path['name'], PATHINFO_EXTENSION);
    $signature_path_path = $upload_dir . $signature_path_name;

    if (!move_uploaded_file($signature_path['tmp_name'], $signature_path_path)) {
        echo json_encode(["success" => false, "message" => "Failed to move uploaded file."]);
        exit();
    }
}

// ---------- INSERT ----------
$sql = "";
$bind_params = [];
$param_types = "";

switch ($target) {
    case 'head-parish':
        $sql = "INSERT INTO head_parish_admins
            (head_parish_admin_fullname, head_parish_admin_email, head_parish_admin_phone, head_parish_admin_role,
             head_parish_admin_password, head_parish_id, signature_path)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        $bind_params = [$admin_fullname, $admin_email, $admin_phone, $admin_role, $default_password, $head_parish_id, $signature_path_name];
        $param_types = "sssssis";
        break;

    case 'sub-parish':
        $sql = "INSERT INTO sub_parish_admins
            (sub_parish_admin_fullname, sub_parish_admin_email, sub_parish_admin_phone, sub_parish_admin_role,
             sub_parish_admin_password, head_parish_id, sub_parish_id, signature_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $bind_params = [$admin_fullname, $admin_email, $admin_phone, $admin_role, $default_password, $head_parish_id, $sub_parish_id, $signature_path_name];
        $param_types = "ssssssis";
        break;

    case 'community':
        $sql = "INSERT INTO community_admins
            (community_admin_fullname, community_admin_email, community_admin_phone, community_admin_role,
             community_admin_password, head_parish_id, sub_parish_id, community_id, signature_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $bind_params = [$admin_fullname, $admin_email, $admin_phone, $admin_role, $default_password, $head_parish_id, $sub_parish_id, $community_id, $signature_path_name];
        $param_types = "sssssiiss";
        break;

    case 'group':
        $sql = "INSERT INTO group_admins
            (group_admin_fullname, group_admin_email, group_admin_phone, group_admin_role,
             group_admin_password, head_parish_id, group_id, signature_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $bind_params = [$admin_fullname, $admin_email, $admin_phone, $admin_role, $default_password, $head_parish_id, $group_id, $signature_path_name];
        $param_types = "ssssssis";
        break;
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
    exit();
}

$stmt->bind_param($param_types, ...$bind_params);

if ($stmt->execute()) {
    // Send SMS only for head-parish admins
    if ($target === 'head-parish') {
        // Replace leading '0' with '255' for the phone number
        if (substr($admin_phone, 0, 1) === '0') {
            $admin_phone = '255' . substr($admin_phone, 1);
        }

        $adminData = [
            'first_name' => explode(' ', $admin_fullname)[0],
            'phone' => $admin_phone,
            'role' => $admin_role
        ];
        sendAdminRegistrationSMS($conn, $head_parish_id, $adminData);
    }

    echo json_encode(["success" => true, "message" => "Admin registered successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to register admin: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
