<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Check session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

// Validate POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

$member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;
$received_letter = isset($_POST['received_letter']) && $_POST['received_letter'] == "1" ? "Yes" : "No";
$local_timestamp = isset($_POST['local_timestamp']) ? $conn->real_escape_string($_POST['local_timestamp']) : null;
$head_parish_id = $_SESSION['head_parish_id'];  // Get the head parish ID from the session

if ($member_id <= 0) {
    echo json_encode(["success" => false, "message" => "Please select a valid member."]);
    exit();
}

if (empty($local_timestamp)) {
    echo json_encode(["success" => false, "message" => "Local timestamp is required."]);
    exit();
}

// Check if record exists
$check_sql = "SELECT status FROM harambee_letter_statuses WHERE member_id = ? AND head_parish_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $member_id, $head_parish_id);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    $check_stmt->bind_result($existing_status);
    $check_stmt->fetch();

    if ($existing_status === $received_letter) {
        echo json_encode(["success" => false, "message" => "Status already recorded as '$received_letter'."]);
    } else {
        // Update
        $update_stmt = $conn->prepare("UPDATE harambee_letter_statuses SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE member_id = ? AND head_parish_id = ?");
        $update_stmt->bind_param("sii", $received_letter, $member_id, $head_parish_id);
        if ($update_stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Status updated to '$received_letter'."]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update status."]);
        }
        $update_stmt->close();
    }
} else {
    // Insert new
    $insert_stmt = $conn->prepare("INSERT INTO harambee_letter_statuses (member_id, status, head_parish_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("issss", $member_id, $received_letter, $head_parish_id, $local_timestamp, $local_timestamp);
    if ($insert_stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Harambee letter status recorded as '$received_letter'."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to record status."]);
    }
    $insert_stmt->close();
}

$check_stmt->close();
$conn->close();
?>
