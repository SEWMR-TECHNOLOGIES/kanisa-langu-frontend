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
    $title = isset($_POST['title']) ? $conn->real_escape_string($_POST['title']) : null;
    $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : null;
    $from_time = isset($_POST['from_time']) ? $conn->real_escape_string($_POST['from_time']) : null;
    $to_time = isset($_POST['to_time']) ? $conn->real_escape_string($_POST['to_time']) : null;
    $participants = isset($_POST['participants']) ? $conn->real_escape_string($_POST['participants']) : null;

    // Input validation
    if (empty($meeting_id)) {
        echo json_encode(["success" => false, "message" => "Meeting ID is required"]);
        exit();
    }

    if (empty($title)) {
        echo json_encode(["success" => false, "message" => "Agenda title is required"]);
        exit();
    }

    if (empty($from_time) || empty($to_time)) {
        echo json_encode(["success" => false, "message" => "From time and To time are required"]);
        exit();
    }

    // Validate time format (24-hour format)
    $from_time_object = DateTime::createFromFormat('H:i', $from_time);
    $to_time_object = DateTime::createFromFormat('H:i', $to_time);

    if (!$from_time_object || $from_time_object->format('H:i') !== $from_time) {
        echo json_encode(["success" => false, "message" => "Invalid From time format. Use HH:MM in 24-hour format."]);
        exit();
    }

    if (!$to_time_object || $to_time_object->format('H:i') !== $to_time) {
        echo json_encode(["success" => false, "message" => "Invalid To time format. Use HH:MM in 24-hour format."]);
        exit();
    }

    // Ensure from_time is less than or equal to to_time
    if ($from_time_object > $to_time_object) {
        echo json_encode(["success" => false, "message" => "From time cannot be greater than To time"]);
        exit();
    }

    // Insert new agenda record
    $insert_sql = "INSERT INTO meeting_agenda (meeting_id, title, description, from_time, to_time, participants)
                    VALUES (?, ?, ?, ?, ?, ?)";

    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("isssss", $meeting_id, $title, $description, $from_time, $to_time, $participants);

    if ($insert_stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Agenda added successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add agenda"]);
    }

    $insert_stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
