<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Ensure the head_parish_id is set in the session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract POST data directly
    $meeting_id = isset($_POST['meeting_id']) ? $conn->real_escape_string($_POST['meeting_id']) : null;
    $note_text = isset($_POST['note_text']) ? $conn->real_escape_string($_POST['note_text']) : null;

    // Input validation
    if (empty($meeting_id)) {
        echo json_encode(["success" => false, "message" => "Meeting ID is required"]);
        exit();
    }

    if (empty($note_text)) {
        echo json_encode(["success" => false, "message" => "Notes text is required"]);
        exit();
    }

    // Insert new notes record
    $insert_sql = "INSERT INTO meeting_notes (meeting_id, note_text) VALUES (?, ?)";

    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("is", $meeting_id, $note_text);

    if ($insert_stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Notes added successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add notes"]);
    }

    $insert_stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
