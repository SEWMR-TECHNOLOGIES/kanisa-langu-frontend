<?php
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $head_parish_id = isset($_GET['headParishId']) ? intval($_GET['headParishId']) : null;

    if (!$head_parish_id) {
        echo json_encode([
            "success" => false,
            "message" => "Missing or invalid head_parish_id parameter."
        ]);
        exit();
    }

    $stmt = $conn->prepare("SELECT topic, created_at, updated_at FROM head_parish_fcm_topics WHERE head_parish_id = ?");
    $stmt->bind_param("i", $head_parish_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $topics = [];

        while ($row = $result->fetch_assoc()) {
            $topics[] = $row['topic'];
        }

        echo json_encode([
            "success" => true,
            "head_parish_id" => $head_parish_id,
            "data" => $topics
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to fetch topics."
        ]);
    }

    $stmt->close();
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method. Use GET."
    ]);
}

$conn->close();
