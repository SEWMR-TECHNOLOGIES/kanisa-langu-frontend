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
    $merchant_code = isset($_POST['merchant_code']) ? $conn->real_escape_string($_POST['merchant_code']) : '';
    $account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : 0;
    $client_id = isset($_POST['client_id']) ? $conn->real_escape_string($_POST['client_id']) : '';
    $client_secret = isset($_POST['client_secret']) ? $conn->real_escape_string($_POST['client_secret']) : '';

    // Validate inputs and return specific error messages
    if (empty($target)) {
        echo json_encode(["success" => false, "message" => "Target is required"]);
        exit();
    }
    if (empty($merchant_code)) {
        echo json_encode(["success" => false, "message" => "Merchant code is required"]);
        exit();
    }
    if (empty($client_id)) {
        echo json_encode(["success" => false, "message" => "Client ID is required"]);
        exit();
    }
    if (empty($client_secret)) {
        echo json_encode(["success" => false, "message" => "Client secret is required"]);
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

    // Determine the target table
    $table = '';
    $foreign_key = '';
    switch ($target) {
        case 'diocese':
            $table = 'diocese_payment_gateway_wallets';
            $foreign_key = 'diocese_id';
            break;
        case 'province':
            $table = 'province_payment_gateway_wallets';
            $foreign_key = 'province_id';
            break;
        case 'head_parish':
            $table = 'head_parish_payment_gateway_wallets';
            $foreign_key = 'head_parish_id';
            break;
        default:
            echo json_encode(["success" => false, "message" => "Invalid target"]);
            exit();
    }

    // Check for duplicate merchant_code for the specific account
    $checkSql = "SELECT COUNT(*) FROM $table WHERE merchant_code = ? AND account_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("si", $merchant_code, $account_id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo json_encode(["success" => false, "message" => "Merchant code already exists for this account"]);
        exit();
    }

    // Insert the new wallet into the appropriate table
    $sql = "INSERT INTO $table (merchant_code, $foreign_key, account_id, client_id, client_secret) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiss", $merchant_code, $reference_id, $account_id, $client_id, $client_secret);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Wallet created successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to create wallet: " . $stmt->error]);
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
