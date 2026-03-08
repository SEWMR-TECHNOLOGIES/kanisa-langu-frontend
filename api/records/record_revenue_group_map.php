<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Ensure head_parish_id is in session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

// Check database connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target = isset($_POST['target']) ? $conn->real_escape_string($_POST['target']) : '';
    $revenue_group_id = isset($_POST['revenue_group_id']) ? intval($_POST['revenue_group_id']) : null;
    $revenue_stream_id = isset($_POST['revenue_stream_id']) ? $conn->real_escape_string($_POST['revenue_stream_id']) : '';

    // Validate the target input
    if (empty($target) || !in_array($target, ['head-parish', 'sub-parish', 'community', 'group'])) {
        echo json_encode(["success" => false, "message" => "Invalid target specified"]);
        exit();
    }
    // Validate the expense group
    if (empty($revenue_group_id)) {
        echo json_encode(["success" => false, "message" => "Revenue Group cannot be blank!"]);
        exit();
    }
    
    // Validate the expense name input
    if (empty($revenue_stream_id)) {
        echo json_encode(["success" => false, "message" => "Revenue Stream cannot be blank!"]);
        exit();
    }

    // Initialize variable for the table name and duplicate check query
    $table = '';
    $duplicate_check_query = '';

    // Determine the target table for expense names
    switch ($target) {
        case 'head-parish':
            $table = 'head_parish_revenue_groups_map';
            break;

        case 'sub-parish':
            $table = 'sub_parish_revenue_groups_map';
            break;

        case 'community':
            $table = 'community_revenue_groups_map';
            break;

        case 'group':
            $table = 'group_revenue_groups_map';
            break;
    }

    // Prepare and execute the duplicate check query
    $duplicate_check_query = "SELECT COUNT(*) FROM $table WHERE revenue_group_id = ? AND revenue_stream_id = ?";
    $stmt = $conn->prepare($duplicate_check_query);
    $stmt->bind_param("is", $revenue_group_id, $revenue_stream_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    // Check for existing expense name
    if ($count > 0) {
        echo json_encode(["success" => false, "message" => "Revenue stream already exists for this revenue group"]);
        exit();
    }

    // Insert the new expense name into the appropriate table
    $insert_query = "INSERT INTO $table (revenue_group_id, revenue_stream_id) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("is", $revenue_group_id, $revenue_stream_id);

    // Execute the insertion
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Revenue Stream mapped successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to map revenue stream: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
