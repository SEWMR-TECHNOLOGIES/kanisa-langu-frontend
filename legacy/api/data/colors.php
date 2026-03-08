<?php
header('Content-Type: application/json');

// Database connection
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Fetch church colors
$sql = "SELECT color_id, color_name, color_code FROM church_colors ORDER BY color_name ASC";
$result = $conn->query($sql);

if ($result) {
    $colors = [];
    while ($row = $result->fetch_assoc()) {
        $colors[] = $row;
    }
    echo json_encode(["success" => true, "data" => $colors]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to fetch colors: " . $conn->error]);
}

$conn->close();
?>
