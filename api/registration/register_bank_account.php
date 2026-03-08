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
    $account_name = isset($_POST['account_name']) ? $conn->real_escape_string($_POST['account_name']) : '';
    $account_number = isset($_POST['account_number']) ? $conn->real_escape_string($_POST['account_number']) : '';
    $bank_id = isset($_POST['bank_id']) ? intval($_POST['bank_id']) : 0;
    $balance = isset($_POST['balance']) ? $_POST['balance'] : '0.00';

    // Validate inputs and return specific error messages
    if (empty($target)) {
        echo json_encode(["success" => false, "message" => "Target is required"]);
        exit();
    }
    if (empty($account_name)) {
        echo json_encode(["success" => false, "message" => "Account name is required"]);
        exit();
    }
    if (empty($account_number)) {
        echo json_encode(["success" => false, "message" => "Account number is required"]);
        exit();
    }
    if ($reference_id <= 0) {
        echo json_encode(["success" => false, "message" => "Reference ID is required and must be greater than 0"]);
        exit();
    }
    if ($bank_id <= 0) {
        echo json_encode(["success" => false, "message" => "Bank name is required"]);
        exit();
    }

    // Validate balance
    if (!is_numeric($balance)) {
        echo json_encode(["success" => false, "message" => "Balance must be a numeric value"]);
        exit();
    }
    $balance = number_format((float)$balance, 2, '.', ''); // Format balance to 2 decimal places

    // Determine the target table
    $table = '';
    $foreign_key = '';
    switch ($target) {
        case 'diocese':
            $table = 'diocese_bank_accounts';
            $foreign_key = 'diocese_id';
            break;
        case 'province':
            $table = 'province_bank_accounts';
            $foreign_key = 'province_id';
            break;
        case 'head_parish':
            $table = 'head_parish_bank_accounts';
            $foreign_key = 'head_parish_id';
            break;
        default:
            echo json_encode(["success" => false, "message" => "Invalid target"]);
            exit();
    }

    // Check for duplicate account_number within the same bank_id in the appropriate table
    $checkSql = "SELECT COUNT(*) FROM $table WHERE account_number = ? AND bank_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("si", $account_number, $bank_id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo json_encode(["success" => false, "message" => "Account number already exists for this bank"]);
        exit();
    }

    // Insert the new bank account into the appropriate table
    $sql = "INSERT INTO $table (account_name, account_number, $foreign_key, bank_id, balance) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiid", $account_name, $account_number, $reference_id, $bank_id, $balance);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Bank account created successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to create bank account: " . $stmt->error]);
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
