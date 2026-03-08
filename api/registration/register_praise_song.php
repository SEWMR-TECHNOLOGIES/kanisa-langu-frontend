<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/validation_functions.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input values
    $song_number = isset($_POST['song_number']) ? (int)$_POST['song_number'] : 0;
    $song_name = isset($_POST['song_name']) ? $conn->real_escape_string($_POST['song_name']) : '';
    $page_number = isset($_POST['page_number']) ? (int)$_POST['page_number'] : 0;

    // Validate mandatory fields
    if ($song_number == 0) {
        echo json_encode(["success" => false, "message" => "Song number is required"]);
        exit();
    }

    if ($song_name === '') {
        echo json_encode(["success" => false, "message" => "Song name is required"]);
        exit();
    }

    if ($page_number == 0) {
        echo json_encode(["success" => false, "message" => "Page number is required"]);
        exit();
    }

    // Check for existing song with the same number
    $checkSql = "SELECT * FROM praise_songs WHERE song_number = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("i", $song_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "A song with this number already exists"]);
        $stmt->close();
        exit();
    }

    // Insert new praise song record
    $sql = "INSERT INTO praise_songs (song_number, song_name, page_number)
            VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $song_number, $song_name, $page_number);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Praise song registered successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to register praise song: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
