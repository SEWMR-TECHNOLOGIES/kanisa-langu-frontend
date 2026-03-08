<?php
header('Content-Type: application/json');
date_default_timezone_set('Africa/Nairobi');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/validation_functions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

if (!isset($_SESSION['head_parish_admin_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized: admin not logged in"]);
    exit();
}

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recorded_by = (int)$_SESSION['head_parish_admin_id'];
    $created_at = date('Y-m-d H:i:s');

    // Clean input
    $title_id = isset($_POST['title_id']) && trim($_POST['title_id']) !== '' ? (int)$_POST['title_id'] : null;
    $first_name = isset($_POST['first_name']) ? ucfirst(strtolower($conn->real_escape_string($_POST['first_name']))) : '';
    $middle_name = isset($_POST['middle_name']) ? ucfirst(strtolower($conn->real_escape_string($_POST['middle_name']))) : null;
    $last_name = isset($_POST['last_name']) ? ucfirst(strtolower($conn->real_escape_string($_POST['last_name']))) : '';
    $date_of_birth = isset($_POST['date_of_birth']) ? $conn->real_escape_string($_POST['date_of_birth']) : '';
    $gender = isset($_POST['gender']) ? $conn->real_escape_string($_POST['gender']) : '';
    $type = isset($_POST['type']) ? $conn->real_escape_string($_POST['type']) : '';
    $head_parish_id = isset($_POST['head_parish_id']) && trim($_POST['head_parish_id']) !== '' ? (int)$_POST['head_parish_id'] : null;
    $envelope_number = isset($_POST['envelope_number']) ? $conn->real_escape_string($_POST['envelope_number']) : null;
    $sub_parish_id = isset($_POST['sub_parish_id']) && trim($_POST['sub_parish_id']) !== '' ? (int)$_POST['sub_parish_id'] : null;
    $community_id = isset($_POST['community_id']) && trim($_POST['community_id']) !== '' ? (int)$_POST['community_id'] : null;
    $status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : 'Active';
    $occupation_id = isset($_POST['occupation_id']) && trim($_POST['occupation_id']) !== '' ? (int)$_POST['occupation_id'] : null;
    $phone = isset($_POST['phone']) ? $conn->real_escape_string($_POST['phone']) : null;
    $email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : null;

    // Validation
    if (empty($first_name)) {
        echo json_encode(["success" => false, "message" => "First name is required"]);
        exit();
    }

    if (empty($last_name)) {
        echo json_encode(["success" => false, "message" => "Last name is required"]);
        exit();
    }

    if (empty($date_of_birth)) {
        echo json_encode(["success" => false, "message" => "Date of birth is required"]);
        exit();
    }

    if (empty($sub_parish_id)) {
        echo json_encode(["success" => false, "message" => "Sub Parish is required"]);
        exit();
    }

    if (empty($community_id)) {
        echo json_encode(["success" => false, "message" => "Community is required"]);
        exit();
    }

    if ($email && !isValidEmail($email)) {
        echo json_encode(["success" => false, "message" => "Invalid email format"]);
        exit();
    }

    if ($phone && !isValidPhone($phone)) {
        echo json_encode(["success" => false, "message" => "Invalid phone format"]);
        exit();
    }
    
    if (empty($type) || !in_array(strtolower($type), ['mgeni', 'mwenyeji'])) {
        echo json_encode(["success" => false, "message" => "Type is required and must be either 'Mgeni' or 'Mwenyeji'"]);
        exit();
    }

    // Replace leading '0' with '255' if phone is valid
    if ($phone) {
        $phone = preg_replace('/^0/', '255', $phone);
    }

    // Age validation
    $dobDate = new DateTime($date_of_birth);
    $currentDate = new DateTime();
    $age = $currentDate->diff($dobDate)->y;
    if ($age < 5) {
        echo json_encode(["success" => false, "message" => "Date of birth must be at least 5 years ago"]);
        exit();
    }

    // Duplicates check
    if ($email) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM church_members WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($countEmail);
        $stmt->fetch();
        $stmt->close();
        if ($countEmail > 0) {
            echo json_encode(["success" => false, "message" => "Email already exists"]);
            exit();
        }
    }

    if ($phone) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM church_members WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $stmt->bind_result($countPhone);
        $stmt->fetch();
        $stmt->close();
        if ($countPhone > 0) {
            echo json_encode(["success" => false, "message" => "Phone number already exists"]);
            exit();
        }
    }

    if ($envelope_number) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM church_members WHERE envelope_number = ?");
        $stmt->bind_param("s", $envelope_number);
        $stmt->execute();
        $stmt->bind_result($countEnvelope);
        $stmt->fetch();
        $stmt->close();
        if ($countEnvelope > 0) {
            echo json_encode(["success" => false, "message" => "Envelope number already exists"]);
            exit();
        }
    }

    $sql = "INSERT INTO church_members (
                title_id, first_name, middle_name, last_name, date_of_birth, gender, type,
                head_parish_id, sub_parish_id, community_id, envelope_number, status,
                occupation_id, phone, email, recorded_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "issssssiiississis",
        $title_id, $first_name, $middle_name, $last_name, $date_of_birth, $gender, $type,
        $head_parish_id, $sub_parish_id, $community_id, $envelope_number, $status,
        $occupation_id, $phone, $email, $recorded_by, $created_at
    );

    if ($stmt->execute()) {
        $member_id = $conn->insert_id;
        $member = getMemberDetails($conn, $member_id)->fetch_assoc();
        if(!empty($phone)){
             sendChurchMemberRegistrationMessage($conn, $member);
        }
        echo json_encode(["success" => true, "message" => "Church member registered successfully", "member_id" => $member_id]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add church member: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
