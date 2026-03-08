<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
date_default_timezone_set('Africa/Nairobi');

if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "We couldn’t identify your parish. Please sign in again."
    ]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedback_type = isset($_POST['feedback_type']) ? $conn->real_escape_string($_POST['feedback_type']) : null;
    $subject = isset($_POST['subject']) ? $conn->real_escape_string($_POST['subject']) : null;
    $message = isset($_POST['message']) ? $conn->real_escape_string($_POST['message']) : null;
    $submitted_by_admin_id = isset($_SESSION['head_parish_admin_id']) ? $_SESSION['head_parish_admin_id'] : null;

    if (empty($feedback_type) || empty($subject) || empty($message)) {
        echo json_encode([
            "success" => false,
            "message" => "Please fill in all required fields: type, subject, and message."
        ]);
        exit();
    }

    $insert_sql = "INSERT INTO head_parish_feedback 
        (head_parish_id, submitted_by_admin_id, feedback_type, subject, message, submitted_at)
        VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($insert_sql);
    $current_time = date('Y-m-d H:i:s');
    $stmt->bind_param("iissss", $head_parish_id, $submitted_by_admin_id, $feedback_type, $subject, $message, $current_time);

    if ($stmt->execute()) {
        sendFeedbackNotification($feedback_type, $subject, $message);

        echo json_encode([
            "success" => true,
            "message" => "Thanks for your feedback! We’ve received it and will use it to improve Kanisa Langu."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Something went wrong while saving your feedback. Please try again."
        ]);
    }

    $stmt->close();
} else {
    echo json_encode([
        "success" => false,
        "message" => "This page only accepts feedback submissions."
    ]);
}

$conn->close();
?>
