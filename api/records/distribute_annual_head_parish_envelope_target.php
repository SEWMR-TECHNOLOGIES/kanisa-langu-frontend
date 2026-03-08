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

// Validate input data
$sub_parish_id = isset($_POST['sub_parish_id']) ? (int)$_POST['sub_parish_id'] : 0;
$from_date = isset($_POST['from_date']) ? $_POST['from_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
$percentage = isset($_POST['percentage']) ? (float)$_POST['percentage'] : 0;

// Ensure valid input
if ($sub_parish_id <= 0 || $percentage <= 0 || $percentage > 100 || empty($from_date) || empty($end_date)) {
    echo json_encode(["success" => false, "message" => "Invalid input. Ensure Sub parish selected, percentage (0-100), and valid dates are provided."]);
    exit();
}

// Extract the year from the provided from_date
$yearFromDate = date('Y', strtotime($from_date));
$yearEndDate = date('Y', strtotime($end_date));

// Ensure the year from both dates is the same
if ($yearFromDate !== $yearEndDate) {
    echo json_encode(["success" => false, "message" => "The year in from_date and end_date must be the same."]);
    exit();
}

// Generate the formatted dates as Y-01-01 and Y-12-31
$formattedFromDate = $yearFromDate . '-01-01';
$formattedEndDate = $yearEndDate . '-12-31';

// Compare the provided dates to ensure they match the generated Y-01-01 and Y-12-31
if ($from_date !== $formattedFromDate || $end_date !== $formattedEndDate) {
    echo json_encode(["success" => false, "message" => "The from_date must be in the format 'YYYY-01-01' and the end_date must be in the format 'YYYY-12-31'."]);
    exit();
}

// Fetch the head parish target for the given date range
$targetSql = "SELECT target FROM head_parish_envelope_targets WHERE head_parish_id = ? AND from_date = ? AND end_date = ?";
$targetStmt = $conn->prepare($targetSql);
$targetStmt->bind_param("iss", $head_parish_id, $formattedFromDate, $formattedEndDate);
$targetStmt->execute();
$targetStmt->bind_result($headParishTarget);
$targetStmt->fetch();
$targetStmt->close();

// Check if a target was found for the given date range
if (empty($headParishTarget)) {
    echo json_encode(["success" => false, "message" => "No head parish target found for the given date range."]);
    exit();
}

// Calculate the distribution amount for the sub-parish based on the percentage
$distributionAmount = ($headParishTarget * $percentage) / 100;

// Check if the distribution already exists for this sub_parish_id, head_parish_id, and date range
$checkSql = "SELECT distribution_id FROM sub_parish_envelope_distributions WHERE sub_parish_id = ? AND head_parish_id = ? AND from_date = ? AND end_date = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("iiss", $sub_parish_id, $head_parish_id, $formattedFromDate, $formattedEndDate);
$checkStmt->execute();
$checkStmt->bind_result($distributionId);
$checkStmt->fetch();
$checkStmt->close();

// If distribution exists, update it, otherwise insert a new record
if ($distributionId) {
    // Update existing distribution, include percentage
    $updateSql = "UPDATE sub_parish_envelope_distributions SET distribution_amount = ?, percentage = ? WHERE distribution_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("dii", $distributionAmount, $percentage, $distributionId);
    
    if ($updateStmt->execute()) {
        echo json_encode(["success" => true, "message" => "Distribution updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update distribution: " . $updateStmt->error]);
    }
    
    $updateStmt->close();
} else {
    // Insert new distribution, include percentage
    $insertSql = "INSERT INTO sub_parish_envelope_distributions (sub_parish_id, head_parish_id, distribution_amount, percentage, from_date, end_date) 
                  VALUES (?, ?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("iiddss", $sub_parish_id, $head_parish_id, $distributionAmount, $percentage, $formattedFromDate, $formattedEndDate);

    if ($insertStmt->execute()) {
        echo json_encode(["success" => true, "message" => "Distribution set successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to set distribution: " . $insertStmt->error]);
    }

    $insertStmt->close();
}

$conn->close();
?>
