<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/encryption_functions.php');

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

    // Accept only api_token and sender_id
    $raw_api_token = isset($_POST['api_token']) ? trim($_POST['api_token']) : '';
    $sender_id = isset($_POST['sender_id']) ? trim($conn->real_escape_string($_POST['sender_id'])) : '';

    // Validate inputs
    if (empty($raw_api_token)) {
        echo json_encode(["success" => false, "message" => "API Token is required"]);
        exit();
    }

    if (empty($sender_id)) {
        echo json_encode(["success" => false, "message" => "Sender ID is required"]);
        exit();
    }

    // Encrypt the API token before saving it
    $api_token = encryptData($raw_api_token);

    // Get current timestamp in Africa/Nairobi
    $datetime = new DateTime("now", new DateTimeZone("Africa/Nairobi"));
    $timestamp = $datetime->format("Y-m-d H:i:s");

    try {
        // Check if there's already an API record for this head parish
        $check_sql = "SELECT COUNT(*) FROM head_parish_sms_api_info WHERE head_parish_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $head_parish_id);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            // Update existing record with Nairobi timestamp
            $update_sql = "UPDATE head_parish_sms_api_info 
                           SET api_token = ?, sender_id = ?, updated_at = ? 
                           WHERE head_parish_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssi", $api_token, $sender_id, $timestamp, $head_parish_id);

            if ($update_stmt->execute()) {
                echo json_encode(["success" => true, "message" => "SMS API information updated successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to update SMS API information: " . $update_stmt->error]);
            }
            $update_stmt->close();
        } else {
            // Insert a new record with Nairobi timestamp
            $insert_sql = "INSERT INTO head_parish_sms_api_info 
                           (head_parish_id, api_token, sender_id, created_at, updated_at) 
                           VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("issss", $head_parish_id, $api_token, $sender_id, $timestamp, $timestamp);

            if ($insert_stmt->execute()) {
                echo json_encode(["success" => true, "message" => "SMS API information added successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to add SMS API information: " . $insert_stmt->error]);
            }
            $insert_stmt->close();
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }

} else {
    // If the request method is not POST
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
