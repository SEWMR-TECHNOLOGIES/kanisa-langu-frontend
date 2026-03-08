<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Check if head_parish_id is in session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

// Check the database connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usage_date = isset($_POST['usage_date']) ? trim($_POST['usage_date']) : '';
    $benchmark = isset($_POST['benchmark']) ? intval($_POST['benchmark']) : 1000;

    // Validate usage_date
    if (empty($usage_date)) {
        echo json_encode(["success" => false, "message" => "Usage date is required"]);
        exit();
    }

    // Generate report URL
    $report_url = "https://kanisalangu.sewmrtechnologies.com/reports/envelope_usage.php?date=" . urlencode($usage_date) . "&benchmark=" . $benchmark;

    echo json_encode([
        "success" => true,
        "message" => "Report URL generated successfully",
        "report_url" => $report_url
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
