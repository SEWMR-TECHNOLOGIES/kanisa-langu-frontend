<?php
header('Content-Type: application/json');

// Include DB connection
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Check DB connection
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit();
}

// Only allow GET method
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT measure_id, unit, meaning FROM unit_of_measure ORDER BY unit ASC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $measures = [];

        while ($row = $result->fetch_assoc()) {
            $measures[] = $row;
        }

        echo json_encode([
            "success" => true,
            "data" => $measures
        ]);
    } else {
        echo json_encode([
            "success" => true,
            "data" => [],
            "message" => "No unit of measures found"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
}

$conn->close();
?>
