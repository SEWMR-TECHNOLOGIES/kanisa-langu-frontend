<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Check if head_parish_id is in session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

// Check the database connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;
    $target = isset($_POST['target']) ? floatval($_POST['target']) : 0.00;
    $from_date = isset($_POST['from_date']) ? $conn->real_escape_string($_POST['from_date']) : null;
    $end_date = isset($_POST['end_date']) ? $conn->real_escape_string($_POST['end_date']) : null;

    // Validate mandatory fields
    if ($member_id <= 0) {
        echo json_encode(["success" => false, "message" => "Please select a valid member."]);
        exit();
    }

    if ($target <= 0) {
        echo json_encode(["success" => false, "message" => "Target must be greater than 0."]);
        exit();
    }

    if (empty($from_date) || empty($end_date)) {
        echo json_encode(["success" => false, "message" => "Both from date and end date are required."]);
        exit();
    }

    // Fetch sub_parish_id and community_id based on member_id
    $location_data = getSubParishAndCommunity($member_id, $conn);
    $sub_parish_id = $location_data['sub_parish_id'];
    $community_id = $location_data['community_id'];

    if (!$sub_parish_id || !$community_id) {
        echo json_encode(["success" => false, "message" => "Sub Parish ID and Community ID could not be found for the member."]);
        exit();
    }

    // Check if the member already has an envelope target within the date range
    $stmt = $conn->prepare("SELECT * FROM envelope_targets WHERE member_id = ? AND from_date = ? AND end_date = ?");
    $stmt->bind_param("iss", $member_id, $from_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the target exists, update it
    if ($result->num_rows > 0) {
        $sql_update = "UPDATE envelope_targets SET target = ?, sub_parish_id = ?, community_id = ? 
                       WHERE member_id = ? AND from_date = ? AND end_date = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("diisss", $target, $sub_parish_id, $community_id, $member_id, $from_date, $end_date);
        if ($stmt_update->execute()) {
            echo json_encode(["success" => true, "message" => "Envelope target updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update envelope target"]);
        }
        $stmt_update->close();
    } else {
        // Insert the new envelope target if none exists
        $sql_insert = "INSERT INTO envelope_targets (member_id, target, from_date, end_date, head_parish_id, sub_parish_id, community_id) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("idssiii", $member_id, $target, $from_date, $end_date, $head_parish_id, $sub_parish_id, $community_id);
        if ($stmt_insert->execute()) {
            echo json_encode(["success" => true, "message" => "Envelope target recorded successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to record envelope target"]);
        }
        $stmt_insert->close();
    }

} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
