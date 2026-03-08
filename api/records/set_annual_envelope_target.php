<?php
header('Content-Type: application/json');
session_start(); // Start session to access session variables
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Check if the database connection was successful
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Ensure only POST requests are allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

// Check if head parish ID is in session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access. Please log in."]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

// Validate input data for target amount and dates
$target = isset($_POST['target']) ? (float)$_POST['target'] : 0;
$from_date = isset($_POST['from_date']) ? $_POST['from_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';

// Ensure target value and dates are valid
if ($target <= 0 || empty($from_date) || empty($end_date)) {
    echo json_encode(["success" => false, "message" => "Invalid input. Ensure target amount and dates are properly provided."]);
    exit();
}

// Extract the year from the provided from_date
$yearFromDate = date('Y', strtotime($from_date));
$yearEndDate = date('Y', strtotime($end_date));

// Ensure the year from both dates is the same
if ($yearFromDate !== $yearEndDate) {
    echo json_encode(["success" => false, "message" => "The year in start and end date must be the same."]);
    exit();
}

// Generate the formatted dates as Y-01-01 and Y-12-31
$formattedFromDate = $yearFromDate . '-01-01';
$formattedEndDate = $yearEndDate . '-12-31';

// Compare the provided dates to ensure they match the generated Y-01-01 and Y-12-31
if ($from_date !== $formattedFromDate || $end_date !== $formattedEndDate) {
    echo json_encode(["success" => false, "message" => "The start date must be in the format 'YYYY-01-01' and the end date must be in the format 'YYYY-12-31'."]);
    exit();
}

// Check if envelope target already exists for the given head parish and date range
$checkSql = "SELECT COUNT(*) FROM head_parish_envelope_targets WHERE head_parish_id = ? AND from_date = ? AND end_date = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("iss", $head_parish_id, $formattedFromDate, $formattedEndDate);
$checkStmt->execute();
$checkStmt->bind_result($count);
$checkStmt->fetch();
$checkStmt->close();

if ($count > 0) {
    // Update existing envelope target
    $updateSql = "UPDATE head_parish_envelope_targets SET target = ? WHERE head_parish_id = ? AND from_date = ? AND end_date = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("diss", $target, $head_parish_id, $formattedFromDate, $formattedEndDate);
    
    if ($updateStmt->execute()) {
        echo json_encode(["success" => true, "message" => "Envelope target updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update envelope target: " . $updateStmt->error]);
    }
    
    $updateStmt->close();
} else {
    // Insert new envelope target
    $insertSql = "INSERT INTO head_parish_envelope_targets (head_parish_id, target, from_date, end_date) VALUES (?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("diss", $head_parish_id, $target, $formattedFromDate, $formattedEndDate);
    
    if ($insertStmt->execute()) {
        echo json_encode(["success" => true, "message" => "Envelope target set successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to set envelope target: " . $insertStmt->error]);
    }
    
    $insertStmt->close();
}

$conn->close();
?>
