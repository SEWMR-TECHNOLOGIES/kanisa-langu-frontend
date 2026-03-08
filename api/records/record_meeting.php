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
    $meeting_title = isset($_POST['meeting_title']) ? $conn->real_escape_string($_POST['meeting_title']) : null;
    $meeting_description = isset($_POST['meeting_description']) ? $conn->real_escape_string($_POST['meeting_description']) : null;
    $meeting_date = isset($_POST['meeting_date']) ? $conn->real_escape_string($_POST['meeting_date']) : null;
    $meeting_place = isset($_POST['meeting_place']) ? $conn->real_escape_string($_POST['meeting_place']) : null;
    $meeting_time = isset($_POST['meeting_time']) ? $conn->real_escape_string($_POST['meeting_time']) : null;

    // Input validation
    if (empty($meeting_title)) {
        echo json_encode(["success" => false, "message" => "Meeting title is required"]);
        exit();
    }

    if (empty($meeting_date)) {
        echo json_encode(["success" => false, "message" => "Meeting date is required"]);
        exit();
    }

    if (empty($meeting_time)) {
        echo json_encode(["success" => false, "message" => "Meeting time is required"]);
        exit();
    }

    if (empty($meeting_place)) {
        echo json_encode(["success" => false, "message" => "Meeting place is required"]);
        exit();
    }

    // Validate date (Format: YYYY-MM-DD)
    $date_object = DateTime::createFromFormat('Y-m-d', $meeting_date);
    if (!$date_object || $date_object->format('Y-m-d') !== $meeting_date) {
        echo json_encode(["success" => false, "message" => "Invalid meeting date format. Use YYYY-MM-DD."]);
        exit();
    }

    // Check if the date is in the past
    $current_date = new DateTime('now');
    if ($date_object < $current_date) {
        echo json_encode(["success" => false, "message" => "Meeting date cannot be in the past"]);
        exit();
    }

    // Validate time (Format: HH:MM)
    $time_object = DateTime::createFromFormat('H:i', $meeting_time);
    if (!$time_object || $time_object->format('H:i') !== $meeting_time) {
        echo json_encode(["success" => false, "message" => "Invalid meeting time format. Use HH:MM in 24-hour format."]);
        exit();
    }

    // Check if a meeting with the same date exists
    $check_sql = "SELECT meeting_id FROM meetings WHERE meeting_date = ? AND head_parish_id = ? AND meeting_title = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("sis", $meeting_date, $head_parish_id, $meeting_title);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        // Update existing meeting
        $check_stmt->bind_result($meeting_id);
        $check_stmt->fetch();

        $update_sql = "UPDATE meetings SET 
            meeting_title = ?, 
            meeting_description = ?, 
            meeting_place = ?, 
            meeting_time = ? 
        WHERE meeting_id = ?";

        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param(
            "ssssi",
            $meeting_title,
            $meeting_description,
            $meeting_place,
            $meeting_time,
            $meeting_id
        );

        if ($update_stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Meeting updated successfully", "meeting_id" => $meeting_id]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update meeting"]);
        }

        $update_stmt->close();
    } else {
        // Insert new record
        $insert_sql = "INSERT INTO meetings (
            meeting_title, meeting_description, meeting_date, meeting_place, meeting_time, head_parish_id
        ) VALUES (?, ?, ?, ?, ?, ?)";

        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param(
            "sssssi",
            $meeting_title,
            $meeting_description,
            $meeting_date,
            $meeting_place,
            $meeting_time,
            $head_parish_id
        );

        if ($insert_stmt->execute()) {
            $meeting_id = $insert_stmt->insert_id;
            echo json_encode(["success" => true, "message" => "Meeting added successfully", "meeting_id" => $meeting_id]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to add meeting"]);
        }

        $insert_stmt->close();
    }

    $check_stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
