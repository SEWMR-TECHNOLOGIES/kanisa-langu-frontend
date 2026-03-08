<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Check if head_parish_id is in session
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

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : 0;
    $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
    $expense_budget_target_amount = isset($_POST['expense_budget_target_amount']) ? floatval($_POST['expense_budget_target_amount']) : 0.00;
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0.00;
    $percentage = isset($_POST['percentage']) ? floatval($_POST['percentage']) : 0.00;
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';

    if ($account_id <= 0) {
        echo json_encode(["success" => false, "message" => "Please select a bank account"]);
        exit();
    }
    if ($sub_parish_id <= 0) {
        echo json_encode(["success" => false, "message" => "Sub Parish is required"]);
        exit();
    }
    if ($expense_budget_target_amount <= 0) {
        echo json_encode(["success" => false, "message" => "Expense budget target amount is required"]);
        exit();
    }
    if (empty($start_date) || empty($end_date)) {
        echo json_encode(["success" => false, "message" => "Start Date and End Date are required"]);
        exit();
    }

    // If percentage is provided, calculate the amount
    if ($percentage > 0 && $percentage <= 100) {
        $amount = ($percentage / 100) * $expense_budget_target_amount;
    }
    
    if ($amount <= 0) {
        echo json_encode(["success" => false, "message" => "Amount must be greater than zero"]);
        exit();
    }

    // Begin transaction
    $conn->begin_transaction();
    
    // Check if a record exists in head_parish_expense_budget_distribution
    $stmt = $conn->prepare("SELECT expense_budget_distribution_id FROM head_parish_expense_budget_distribution WHERE bank_account_id = ? AND head_parish_id = ? AND sub_parish_id = ? AND expense_budget_start_date = ? AND expense_budget_end_date = ?");
    $stmt->bind_param("iiiss", $account_id, $head_parish_id, $sub_parish_id, $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing record
        $stmt = $conn->prepare("UPDATE head_parish_expense_budget_distribution SET expense_budget_amount = ? WHERE bank_account_id = ? AND head_parish_id = ? AND sub_parish_id = ? AND expense_budget_start_date = ? AND expense_budget_end_date = ?");
        $stmt->bind_param("diiiss", $amount, $account_id, $head_parish_id, $sub_parish_id, $start_date, $end_date);
        $message = 'Updated';
    } else {
        // Insert new record
        $stmt = $conn->prepare("INSERT INTO head_parish_expense_budget_distribution (head_parish_id, bank_account_id, sub_parish_id, expense_budget_amount, expense_budget_start_date, expense_budget_end_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiidss", $head_parish_id, $account_id, $sub_parish_id, $amount, $start_date, $end_date);
        $message = 'Recorded';
    }

    if ($stmt->execute()) {
        $conn->commit(); // Commit transaction
        echo json_encode(["success" => true, "message" => "Expense budget distribution $message successfully"]);
    } else {
        $conn->rollback(); // Rollback on failure
        echo json_encode(["success" => false, "message" => "Failed to record distribution: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
