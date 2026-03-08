<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Check the database connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target = isset($_POST['target']) ? $conn->real_escape_string($_POST['target']) : '';
    $reference_id = isset($_POST['reference_id']) ? intval($_POST['reference_id']) : 0;
    $revenue_stream_name = isset($_POST['revenue_stream_name']) ? $conn->real_escape_string($_POST['revenue_stream_name']) : '';
    $account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : 0;

    // Validate inputs and return specific error messages
    if (empty($target)) {
        echo json_encode(["success" => false, "message" => "Target is required"]);
        exit();
    }
    if (empty($revenue_stream_name)) {
        echo json_encode(["success" => false, "message" => "Income stream name is required"]);
        exit();
    }
    if ($reference_id <= 0) {
        echo json_encode(["success" => false, "message" => "Reference ID is required and must be greater than 0"]);
        exit();
    }

    if ($account_id <= 0) {
        echo json_encode(["success" => false, "message" => "Bank Account is required"]);
        exit();
    }
    // Determine the target table and foreign key
    $table = '';
    $foreign_key = '';
    switch ($target) {
        case 'diocese':
            $table = 'diocese_revenue_streams';
            $foreign_key = 'diocese_id';
            break;
        case 'province':
            $table = 'province_revenue_streams';
            $foreign_key = 'province_id';
            break;
        case 'head_parish':
            $table = 'head_parish_revenue_streams';
            $foreign_key = 'head_parish_id';
            break;
        default:
            echo json_encode(["success" => false, "message" => "Invalid target"]);
            exit();
    }

    // Check for duplicate revenue_stream_name within the same diocese, province, or head parish
    $checkSql = "SELECT COUNT(*) FROM $table WHERE revenue_stream_name = ? AND $foreign_key = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("si", $revenue_stream_name, $reference_id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo json_encode(["success" => false, "message" => "Income stream name already exists for this target"]);
        exit();
    }

    // Insert the new revenue stream into the appropriate table
    $sql = "INSERT INTO $table (revenue_stream_name, account_id, $foreign_key) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $revenue_stream_name, $account_id, $reference_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Revenue stream recorded successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to record revenue stream: " . $stmt->error]);
    }

    // Close the statement
    $stmt->close();
} else {
    // If the request method is not POST
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
