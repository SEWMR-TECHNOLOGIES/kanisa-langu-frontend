<?php
header('Content-Type: application/json');
date_default_timezone_set('Africa/Nairobi');

session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/validation_functions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

$updated_by = isset($_SESSION['head_parish_admin_id']) ? (int)$_SESSION['head_parish_admin_id'] : null;
$updated_at = date('Y-m-d H:i:s');

if (!isset($_SESSION['head_parish_admin_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized: admin not logged in"]);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the member ID (assuming it's passed in the request to update the member)
    $memberId = isset($_POST['memberId']) ? (int)$_POST['memberId'] : null;

    // Check if member ID is provided
    if (!$memberId) {
        echo json_encode(["success" => false, "message" => "Member ID is required"]);
        exit();
    }

    // Get updated values
    $first_name = isset($_POST['first_name']) ? ucfirst(strtolower($conn->real_escape_string($_POST['first_name']))) : '';
    $middle_name = isset($_POST['middle_name']) ? ucfirst(strtolower($conn->real_escape_string($_POST['middle_name']))) : null;
    $last_name = isset($_POST['last_name']) ? ucfirst(strtolower($conn->real_escape_string($_POST['last_name']))) : '';
    $phone = isset($_POST['phone']) ? $conn->real_escape_string($_POST['phone']) : null;
    $envelope_number = isset($_POST['envelopeNumber']) ? $conn->real_escape_string($_POST['envelopeNumber']) : null;

    // Validate mandatory fields
    if (empty($first_name)) {
        echo json_encode(["success" => false, "message" => "First name is required"]);
        exit();
    }

    if (empty($last_name)) {
        echo json_encode(["success" => false, "message" => "Last name is required"]);
        exit();
    }

    if (empty($envelope_number)) {
        echo json_encode(["success" => false, "message" => "Envelope number is required"]);
        exit();
    }

    if ($phone && !isValidPhone($phone)) {
        echo json_encode(["success" => false, "message" => "Invalid phone format. Phone must start with '0' followed by '7' or '6' and have 10 digits (e.g., 0612345678)"]);
        exit();
    }

    // Replace leading '0' with '255' if phone is valid
    if ($phone) {
        $phone = preg_replace('/^0/', '255', $phone);
    }

    // Check if the phone number is already taken by another member
    if ($phone) {
        $checkPhoneSql = "SELECT COUNT(*) FROM church_members WHERE phone = ? AND member_id != ?";
        $stmt = $conn->prepare($checkPhoneSql);
        $stmt->bind_param("si", $phone, $memberId);
        $stmt->execute();
        $stmt->bind_result($countPhone);
        $stmt->fetch();
        $stmt->close();
        if ($countPhone > 0) {
            echo json_encode(["success" => false, "message" => "Phone number already exists"]);
            exit();
        }
    }

    // Retrieve the current envelope number for comparison
    $currentEnvelopeSql = "SELECT envelope_number FROM church_members WHERE member_id = ?";
    $stmt = $conn->prepare($currentEnvelopeSql);
    $stmt->bind_param("i", $memberId);
    $stmt->execute();
    $stmt->bind_result($current_envelope_number);
    $stmt->fetch();
    $stmt->close();

    // Check if the envelope number has changed
    $envelopeNumberChanged = $current_envelope_number !== $envelope_number;

    // Check if the new envelope number is already taken by another member
    $checkEnvelopeSql = "SELECT COUNT(*) FROM church_members WHERE envelope_number = ? AND member_id != ?";
    $stmt = $conn->prepare($checkEnvelopeSql);
    $stmt->bind_param("si", $envelope_number, $memberId);
    $stmt->execute();
    $stmt->bind_result($countEnvelope);
    $stmt->fetch();
    $stmt->close();
    if ($countEnvelope > 0) {
        echo json_encode(["success" => false, "message" => "Envelope number already exists"]);
        exit();
    }

    // Update the member in the database
    $updateSql = "UPDATE church_members 
                  SET first_name = ?, middle_name = ?, last_name = ?, phone = ?, envelope_number = ?, 
                      updated_by = ?, updated_at = ? 
                  WHERE member_id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("sssssisi", $first_name, $middle_name, $last_name, $phone, $envelope_number, $updated_by, $updated_at, $memberId);


    if ($stmt->execute()) {
        $member = getMemberDetails($conn, $memberId)->fetch_assoc(); 
        if (!$member) {
            echo json_encode(["success" => false, "message" => "Failed to fetch updated member details"]);
            exit();
        }

        if ($envelopeNumberChanged) {
            sendChurchMemberEnvelopeUpdateMessage($conn, $member, $current_envelope_number, $envelope_number);
        } else {
            sendChurchMemberEnvelopeUpdateMessage($conn, $member, $current_envelope_number);
        }

        echo json_encode(["success" => true, "message" => "Church member updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update church member: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
