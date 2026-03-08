<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

date_default_timezone_set('Africa/Nairobi');

if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $meeting_id   = isset($_POST['meeting_id']) ? (int)$_POST['meeting_id'] : null;
    $minutes_text = isset($_POST['minutes_text']) ? trim($_POST['minutes_text']) : null;

    if (empty($meeting_id)) {
        echo json_encode(["success" => false, "message" => "Meeting ID is required"]);
        exit();
    }

    if (empty($minutes_text)) {
        echo json_encode(["success" => false, "message" => "Minutes text is required"]);
        exit();
    }

    $now = date('Y-m-d H:i:s'); // Nairobi time

    $insert_sql = "INSERT INTO meeting_minutes (meeting_id, minutes_text, created_at, updated_at)
                   VALUES (?, ?, ?, ?)";

    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("isss", $meeting_id, $minutes_text, $now, $now);

    if ($insert_stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Minutes added successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add minutes", "error" => $conn->error]);
    }

    $insert_stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
