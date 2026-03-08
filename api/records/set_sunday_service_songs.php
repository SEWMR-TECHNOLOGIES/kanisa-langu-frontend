<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Ensure the head_parish_id is set in session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    $song_id = isset($_POST['song_id']) ? intval($_POST['song_id']) : 0;

    // Validate required fields
    if ($service_id <= 0 || $song_id <= 0) {
        echo json_encode(["success" => false, "message" => "Service and Song are required"]);
        exit();
    }

    // Check if the song is already added to the service
    $check_sql = "SELECT COUNT(*) as count FROM service_songs WHERE service_id = ? AND song_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $service_id, $song_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $row = $check_result->fetch_assoc();
    $check_stmt->close();

    if ($row['count'] > 0) {
        echo json_encode(["success" => false, "message" => "This song is already added to this service"]);
        exit();
    }

    // Insert the song into the service
    $sql = "INSERT INTO service_songs (service_id, song_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $service_id, $song_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Song added to service successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add song to service"]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
