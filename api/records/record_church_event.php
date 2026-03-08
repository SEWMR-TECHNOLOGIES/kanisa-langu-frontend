<?php 
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

date_default_timezone_set('Africa/Nairobi');

if (!isset($_SESSION['head_parish_id']) || !isset($_SESSION['head_parish_admin_id'])) {
    echo json_encode(["success" => false, "message" => "Session data is incomplete"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];
$head_parish_admin_id = $_SESSION['head_parish_admin_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($conn->real_escape_string($_POST['title'])) : null;
    $description = isset($_POST['description']) ? trim($conn->real_escape_string($_POST['description'])) : null;
    $event_date = isset($_POST['event_date']) ? trim($conn->real_escape_string($_POST['event_date'])) : null;
    $end_date = isset($_POST['end_date']) && $_POST['end_date'] !== '' ? $_POST['end_date'] : null;
    $start_time = isset($_POST['start_time']) ? trim($conn->real_escape_string($_POST['start_time'])) : null;
    $end_time = isset($_POST['end_time']) ? trim($conn->real_escape_string($_POST['end_time'])) : null;
    $location = isset($_POST['location']) ? trim($conn->real_escape_string($_POST['location'])) : null;
    $target_audience = isset($_POST['target_audience']) ? trim($conn->real_escape_string($_POST['target_audience'])) : null;
    $notes = isset($_POST['notes']) ? trim($conn->real_escape_string($_POST['notes'])) : null;

    // Validation
    if (empty($title)) {
        echo json_encode(["success" => false, "message" => "Please provide the event title"]);
        exit();
    }

    if (empty($description)) {
        echo json_encode(["success" => false, "message" => "Please provide the event description"]);
        exit();
    }

    if (empty($event_date)) {
        echo json_encode(["success" => false, "message" => "Please provide the event date"]);
        exit();
    }

    $event_date_obj = DateTime::createFromFormat('Y-m-d', $event_date, new DateTimeZone('Africa/Nairobi'));
    if (!$event_date_obj || $event_date_obj->format('Y-m-d') !== $event_date) {
        echo json_encode(["success" => false, "message" => "Invalid start date format. Use YYYY-MM-DD"]);
        exit();
    }

    $now = new DateTime('today', new DateTimeZone('Africa/Nairobi'));
    if ($event_date_obj < $now) {
        echo json_encode(["success" => false, "message" => "Event start date cannot be in the past"]);
        exit();
    }

    if (!empty($end_date)) {
        $end_date_obj = DateTime::createFromFormat('Y-m-d', $end_date, new DateTimeZone('Africa/Nairobi'));
        if (!$end_date_obj || $end_date_obj->format('Y-m-d') !== $end_date) {
            echo json_encode(["success" => false, "message" => "Invalid end date format. Use YYYY-MM-DD"]);
            exit();
        }

        if ($end_date_obj < $event_date_obj) {
            echo json_encode(["success" => false, "message" => "End date cannot be before start date"]);
            exit();
        }
    }

    if (empty($start_time)) {
        echo json_encode(["success" => false, "message" => "Please provide the event start time"]);
        exit();
    }

    $start_time_obj = DateTime::createFromFormat('H:i', $start_time, new DateTimeZone('Africa/Nairobi'));
    if (!$start_time_obj || $start_time_obj->format('H:i') !== $start_time) {
        echo json_encode(["success" => false, "message" => "Invalid start time format. Use HH:MM in 24-hour format"]);
        exit();
    }

    if (empty($end_time)) {
        echo json_encode(["success" => false, "message" => "Please provide the event end time"]);
        exit();
    }

    $end_time_obj = DateTime::createFromFormat('H:i', $end_time, new DateTimeZone('Africa/Nairobi'));
    if (!$end_time_obj || $end_time_obj->format('H:i') !== $end_time) {
        echo json_encode(["success" => false, "message" => "Invalid end time format. Use HH:MM in 24-hour format"]);
        exit();
    }

    if (empty($location)) {
        echo json_encode(["success" => false, "message" => "Please provide the event location"]);
        exit();
    }

    if (empty($target_audience)) {
        echo json_encode(["success" => false, "message" => "Please provide the target audience"]);
        exit();
    }

    // Get current datetime in Africa/Nairobi timezone for created_at and updated_at
    $nowDatetime = (new DateTime('now', new DateTimeZone('Africa/Nairobi')))->format('Y-m-d H:i:s');

    $insert_sql = "INSERT INTO church_events (
        title, description, event_date, end_date, start_time, end_time, location,
        created_by, head_parish_id, target_audience, notes, created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($insert_sql);

    $stmt->bind_param(
        "sssssssiissss",
        $title,
        $description,
        $event_date,
        $end_date,
        $start_time,
        $end_time,
        $location,
        $head_parish_admin_id,
        $head_parish_id,
        $target_audience,
        $notes,
        $nowDatetime,
        $nowDatetime
    );

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Event added successfully", "event_id" => $stmt->insert_id]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add event"]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Only POST requests are allowed"]);
}

$conn->close();
?>
